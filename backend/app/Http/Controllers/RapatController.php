<?php

namespace App\Http\Controllers;

use App\Http\Resources\RapatResource;
use App\Models\Komunitas;
use App\Models\Rapat;
use App\Models\RapatMember;
use App\Services\QrPresensi;
use App\Services\RoleResolver;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RapatController extends Controller
{
    public function __construct(private readonly QrPresensi $qr) {}

    /** GET /rapat — filter komunitas & status. */
    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return RapatResource::collection(
            Rapat::query()
                ->when($request->filled('komunitas'), fn ($q) => $q->whereHas(
                    'komunitas',
                    fn ($k) => $k->where('kode', $request->string('komunitas')->upper())
                ))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->orderByDesc('tanggal')
                ->paginate($perPage)
        );
    }

    /** POST /rapat — qr_secret digenerate otomatis, tidak pernah diekspos (US-14). */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $rapat = Rapat::query()->create([
            ...collect($data)->except('member_ids')->all(),
            'qr_secret' => Str::random(40),
            'user_id' => $request->user()->id,
        ]);

        foreach ($data['member_ids'] ?? [] as $memberId) {
            RapatMember::query()->create(['rapat_id' => $rapat->id, 'member_id' => $memberId]);
        }

        return response()->json(RapatResource::make($rapat)->resolve(), 201);
    }

    /** GET /rapat/{id} — non-anggota komunitas terkait → 403 (US-14). */
    public function show(Request $request, Rapat $rapat)
    {
        $this->pastikanBolehLihat($request->user(), $rapat);

        return response()->json(RapatResource::make($rapat->load('peserta.member'))->resolve());
    }

    /** PUT /rapat/{id} */
    public function update(Request $request, Rapat $rapat)
    {
        $this->pastikanBolehKelola($request->user(), $rapat);
        $data = $this->validated($request);

        $rapat->update(collect($data)->except('member_ids')->all());

        if (array_key_exists('member_ids', $data)) {
            $aktif = $rapat->peserta->keyBy('member_id');
            foreach ($data['member_ids'] as $memberId) {
                RapatMember::query()->firstOrCreate([
                    'rapat_id' => $rapat->id,
                    'member_id' => $memberId,
                ]);
            }
            $aktif->except($data['member_ids'])->each(fn ($p) => $p->delete());
        }

        return response()->json(RapatResource::make($rapat->load('peserta.member'))->resolve());
    }

    /** DELETE /rapat/{id} */
    public function destroy(Request $request, Rapat $rapat): Response
    {
        $this->pastikanBolehKelola($request->user(), $rapat);
        $rapat->delete();

        return response()->noContent();
    }

    /** PUT /rapat/{id}/notulen — teks + lampiran PDF ≤10MB (ADR D5). */
    public function simpanNotulen(Request $request, Rapat $rapat)
    {
        $this->pastikanBolehKelola($request->user(), $rapat);

        $data = $request->validate([
            'notulen' => ['nullable', 'string'],
            'lampiran_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        if ($request->hasFile('lampiran_file')) {
            if ($rapat->lampiran_path !== null) {
                Storage::disk('public')->delete($rapat->lampiran_path);
            }
            $rapat->lampiran_path = $request->file('lampiran_file')->store('rapat/lampiran', 'public');
        }

        $rapat->notulen = $data['notulen'] ?? $rapat->notulen;
        $rapat->save();

        return response()->json(RapatResource::make($rapat)->resolve());
    }

    /**
     * GET /rapat/{id}/qr — payload HMAC berotasi 60s, tanpa write DB (US-15).
     */
    public function qr(Request $request, Rapat $rapat): JsonResponse
    {
        $this->pastikanBolehKelola($request->user(), $rapat);

        return response()->json($this->qr->buatPayload($rapat));
    }

    /**
     * POST /rapat/{id}/absen — presensi via scan QR.
     * 422 token invalid · 410 kedaluwarsa/jendela tutup · 409 sudah absen.
     */
    public function absen(Request $request, Rapat $rapat): JsonResponse
    {
        $this->pastikanBolehLihat($request->user(), $rapat);

        $data = $request->validate([
            'token' => ['required', 'string'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $this->qr->jendelaAktif($rapat)) {
            throw new Problem('jendela-tutup', 'Jendela absensi tidak aktif', 410, 'Absensi belum dibuka atau sudah ditutup.');
        }

        $statusToken = $this->qr->verifikasi($rapat, $data['token']);

        if ($statusToken === 'invalid') {
            throw Problem::validation(['token' => ['Kode tidak dikenali.']], 'Token QR tidak valid.');
        }

        if ($statusToken === 'expired') {
            throw new Problem('qr-expired', 'QR sudah berganti', 410, 'QR sudah ganti — coba scan lagi.');
        }

        $peserta = RapatMember::query()
            ->where('rapat_id', $rapat->id)
            ->where('member_id', $request->user()->member?->id)
            ->first();

        if ($peserta !== null && $peserta->kehadiran === 'hadir') {
            throw new Problem('sudah-absen', 'Sudah presensi', 409, 'Kamu sudah absen tadi 😄');
        }

        $peserta ??= RapatMember::query()->create([
            'rapat_id' => $rapat->id,
            'member_id' => $request->user()->member->id,
        ]);

        $peserta->update([
            'kehadiran' => 'hadir',
            'waktu_absen' => now(),
            'catatan' => $data['catatan'] ?? null,
        ]);

        return response()->json([
            'kehadiran' => 'hadir',
            'waktu' => now()->toISOString(),
        ]);
    }

    /** GET /rapat/{id}/rekap — statistik kehadiran (US-16). */
    public function rekap(Request $request, Rapat $rapat): JsonResponse
    {
        $this->pastikanBolehLihat($request->user(), $rapat);

        $peserta = $rapat->peserta()->with('member:id,nim,nama')->get();
        $total = $peserta->count();

        $hitung = $peserta->groupBy('kehadiran')->map->count();
        $hadir = $hitung->get('hadir', 0);

        return response()->json([
            'total_peserta' => $total,
            'hadir' => $hadir,
            'tidak_hadir' => $hitung->get('tidak', 0),
            'izin' => $hitung->get('izin', 0),
            'persentase' => $total > 0 ? round($hadir / $total * 100, 1) : 0.0,
            'rincian' => $peserta->map(fn ($p) => [
                'member' => $p->member ? [
                    'nim' => $p->member->nim,
                    'nama' => $p->member->nama,
                ] : null,
                'kehadiran' => $p->kehadiran,
                'catatan' => $p->catatan,
            ])->values(),
        ]);
    }

    private function pastikanBolehKelola($user, Rapat $rapat): void
    {
        if ($rapat->komunitas_id === null) {
            Gate::authorize('kelola-struktur');

            return;
        }

        Gate::authorize('pengurus-komunitas', Komunitas::query()->find($rapat->komunitas_id)?->kode ?? '');
    }

    private function pastikanBolehLihat($user, Rapat $rapat): void
    {
        if ($rapat->komunitas_id === null || app(RoleResolver::class)->isAdminPusat($user)) {
            return;
        }

        $disetujui = $user->member?->keanggotaanKomunitas()
            ->where('komunitas_id', $rapat->komunitas_id)
            ->where('status', 'disetujui')
            ->exists() ?? false;

        $pengurusKomunitas = $user->can('pengurus-komunitas', Komunitas::query()->find($rapat->komunitas_id)?->kode ?? '');

        if (! $disetujui && ! $pengurusKomunitas) {
            throw Problem::forbidden('Rapat ini khusus anggota komunitas terkait.');
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'judul' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'string', 'max:255'],
            'tanggal' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'date'],
            'jam_mulai' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'tempat' => ['nullable', 'string', 'max:255'],
            'agenda' => ['nullable', 'string'],
            'komunitas_id' => ['nullable', Rule::exists('komunitas', 'id')],
            'status' => ['sometimes', Rule::in(['dijadwalkan', 'selesai', 'dibatalkan'])],
            'member_ids' => ['sometimes', 'array'],
            'member_ids.*' => [Rule::exists('members', 'id')],
        ]);
    }
}
