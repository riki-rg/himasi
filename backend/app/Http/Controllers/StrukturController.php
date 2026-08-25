<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StrukturController extends Controller
{
    /** GET /periodes */
    public function periodes(): JsonResponse
    {
        return response()->json(
            Periode::query()->orderByDesc('tanggal_mulai')->get()
                ->map(fn ($p) => $this->periodeResource($p))->values()
        );
    }

    /** POST /periodes — periode baru otomatis aktif; aktif lama diarsipkan. */
    public function storePeriode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
        ]);

        $periode = DB::transaction(function () use ($data) {
            Periode::query()->where('status', 'aktif')->update(['status' => 'arsip']);

            return Periode::query()->create([...$data, 'status' => 'aktif']);
        });

        return response()->json($this->periodeResource($periode), 201);
    }

    /** POST /periodes/{id}/arsipkan — tutup periode read-only (ADR D3). */
    public function arsipkan(Periode $periode): JsonResponse
    {
        if ($periode->status === 'arsip') {
            throw Problem::conflict('Periode sudah berstatus arsip.');
        }

        $periode->update(['status' => 'arsip']);

        return response()->json($this->periodeResource($periode));
    }

    /** GET /periodes/{id}/divisi */
    public function divisis(Periode $periode): JsonResponse
    {
        return response()->json(
            $periode->divisis()->with('komunitas:id,kode,nama')->orderBy('urutan')->get()
                ->map(fn ($d) => $this->divisiResource($d))->values()
        );
    }

    /** POST /periodes/{id}/divisi — ditolak bila periode arsip (ADR D3). */
    public function storeDivisi(Request $request, Periode $periode): JsonResponse
    {
        $this->pastikanTidakArsip($periode);

        $data = $request->validate([
            'komunitas_id' => ['required', Rule::exists('komunitas', 'id')],
            'nama' => ['required', 'string', 'max:255'],
            'urutan' => ['sometimes', 'integer', 'min:1'],
        ]);

        $divisi = Divisi::query()->create([
            ...$data,
            'urutan' => $data['urutan'] ?? $periode->divisis()->count() + 1,
            'periode_id' => $periode->id,
        ]);

        return response()->json($this->divisiResource($divisi), 201);
    }

    /** GET /divisi/{id}/jabatan */
    public function jabatans(Divisi $divisi): JsonResponse
    {
        return response()->json(
            $divisi->jabatans()->orderBy('urutan')->get()
                ->map(fn ($j) => $this->jabatanResource($j))->values()
        );
    }

    /** POST /divisi/{id}/jabatan */
    public function storeJabatan(Request $request, Divisi $divisi): JsonResponse
    {
        $this->pastikanTidakArsip($divisi->periode);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tingkat' => ['sometimes', Rule::in(['utama', 'staf', 'anggota'])],
            'urutan' => ['sometimes', 'integer', 'min:1'],
        ]);

        $jabatan = Jabatan::query()->create([
            ...$data,
            'tingkat' => $data['tingkat'] ?? 'staf',
            'urutan' => $data['urutan'] ?? $divisi->jabatans()->count() + 1,
            'divisi_id' => $divisi->id,
        ]);

        return response()->json($this->jabatanResource($jabatan), 201);
    }

    /** POST /penugasan — tunjuk anggota menduduki jabatan. Duplikat → 422. */
    public function storePenugasan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'member_id' => ['required', Rule::exists('members', 'id')],
            'jabatan_id' => ['required', Rule::exists('jabatans', 'id')],
            'periode_id' => ['required', Rule::exists('periodes', 'id')],
        ]);

        $periode = Periode::query()->findOrFail($data['periode_id']);
        $this->pastikanTidakArsip($periode);

        $request->validate([
            'member_id' => [
                Rule::unique('penugasans')->where(
                    fn ($q) => $q->where('jabatan_id', $data['jabatan_id'])
                        ->where('periode_id', $data['periode_id'])
                ),
            ],
        ], [], ['member_id' => 'penugasan']);

        $penugasan = Penugasan::query()->create($data);

        return response()->json($this->penugasanResource($penugasan), 201);
    }

    /** DELETE /penugasan/{id} — anggota hilang dari struktur, datanya utuh. */
    public function destroyPenugasan(Penugasan $penugasan): Response
    {
        $this->pastikanTidakArsip($penugasan->periode);

        $penugasan->delete();

        return response()->noContent();
    }

    /**
     * GET /publik/struktur — tree divisi > jabatan > pengurus.
     * Default periode aktif; fallback arsip terakhir; filter ?komunitas=KODE.
     */
    public function strukturPublik(Request $request): JsonResponse
    {
        $kode = $request->string('komunitas')->upper()->toString();

        $periode = $request->filled('periode')
            ? Periode::query()->findOrFail($request->integer('periode'))
            : (Periode::aktif() ?? Periode::query()->where('status', 'arsip')
                ->orderByDesc('tanggal_selesai')->first());

        if ($periode === null) {
            return response()->json([]);
        }

        return response()->json(
            Divisi::query()
                ->where('periode_id', $periode->id)
                ->when(
                    $kode !== '',
                    fn ($q) => $q->whereHas('komunitas', fn ($k) => $k->where('kode', $kode))
                )
                ->with(['komunitas:id,kode,nama'])
                ->orderBy('urutan')
                ->get()
                ->map(function (Divisi $divisi) {
                    return [
                        'divisi' => $this->divisiResource($divisi),
                        'jabatan' => $divisi->jabatans()
                            ->with(['penugasans.member'])
                            ->orderBy('urutan')
                            ->get()
                            ->map(function (Jabatan $jabatan) {
                                return [
                                    ...$this->jabatanResource($jabatan),
                                    'pengurus' => $jabatan->penugasans
                                        ->filter(fn ($p) => $p->member !== null)
                                        ->map(fn ($p) => [
                                            'nim' => $p->member->nim,
                                            'nama' => $p->member->nama,
                                            'foto_path' => $p->member->foto_path,
                                            'link_instagram' => $p->member->link_instagram,
                                        ])->values(),
                                ];
                            })->values(),
                    ];
                })->values()
        );
    }

    private function pastikanTidakArsip(Periode $periode): void
    {
        if ($periode->status === 'arsip') {
            throw Problem::conflict('Periode sudah diarsipkan — data struktur bersifat read-only.');
        }
    }

    private function periodeResource(Periode $p): array
    {
        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'tanggal_mulai' => $p->tanggal_mulai?->toDateString(),
            'tanggal_selesai' => $p->tanggal_selesai?->toDateString(),
            'status' => $p->status,
        ];
    }

    private function divisiResource(Divisi $d): array
    {
        return [
            'id' => $d->id,
            'periode_id' => $d->periode_id,
            'komunitas' => $d->komunitas ? [
                'id' => $d->komunitas->id,
                'kode' => $d->komunitas->kode,
                'nama' => $d->komunitas->nama,
            ] : null,
            'nama' => $d->nama,
            'urutan' => $d->urutan,
        ];
    }

    private function jabatanResource(Jabatan $j): array
    {
        return [
            'id' => $j->id,
            'divisi_id' => $j->divisi_id,
            'nama' => $j->nama,
            'tingkat' => $j->tingkat,
            'urutan' => $j->urutan,
        ];
    }

    private function penugasanResource(Penugasan $p): array
    {
        return [
            'id' => $p->id,
            'member_id' => $p->member_id,
            'jabatan_id' => $p->jabatan_id,
            'periode_id' => $p->periode_id,
        ];
    }
}
