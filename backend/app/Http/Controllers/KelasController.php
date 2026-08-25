<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Kelas;
use App\Models\Komunitas;
use App\Models\Materi;
use App\Services\RoleResolver;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    /** GET /kelass — pengelolaan; pengurus scoped komunitasnya. */
    public function index(Request $request)
    {
        $kode = $this->resolveKomunitas($request);

        return response()->json(Kelas::query()
            ->when($kode !== null, fn ($q) => $q->whereHas('komunitas', fn ($k) => $k->where('kode', $kode)))
            ->when($request->filled('q'), fn ($q) => $q->where('nama', 'like', '%'.$request->string('q').'%'))
            ->withCount('materis')
            ->get()
            ->map(fn ($k) => [...$this->resource($k), 'jumlah_materi' => $k->materis_count])
            ->values());
    }

    /** POST /kelass */
    public function store(Request $request)
    {
        [$data, $kode] = $this->validated($request);

        if (! is_string($kode)) {
            throw Problem::validation(['komunitas_id' => ['Wajib menentukan komunitas.']]);
        }

        return response()->json($this->resource(
            Kelas::query()->create([...$data, 'komunitas_id' => Komunitas::idByKode((string) $kode)])
        ), 201);
    }

    /**
     * GET /kelass/{id} — detail + materi; non-member komunitas → 403 (US-24).
     */
    public function show(Request $request, Kelas $kelas): JsonResponse
    {
        $boleh = app(RoleResolver::class)->isAdminPusat($request->user())
            || Gate::forUser($request->user())->allows('pengurus-komunitas', $kelas->komunitas?->kode ?? '')
            || $this->memberDisetujui($request->user(), $kelas);

        if (! $boleh) {
            throw Problem::forbidden('Materi kelas khusus anggota komunitas yang sudah disetujui.');
        }

        return response()->json([
            ...$this->resource($kelas),
            'pengajar' => $this->pengajar($kelas),
            'materis' => $kelas->materis->map(fn ($m) => $this->materiResource($m))->values(),
        ]);
    }

    /** PUT /kelass/{id} */
    public function update(Request $request, Kelas $kelas): JsonResponse
    {
        $this->pastikanBolehKelola($request, $kelas);
        [$data] = $this->validated($request, $kelas);

        $kelas->update($data);

        return response()->json($this->resource($kelas));
    }

    /** DELETE /kelass/{id} — cascade materinya. */
    public function destroy(Request $request, Kelas $kelas): Response
    {
        $this->pastikanBolehKelola($request, $kelas);

        Storage::disk('public')->delete($kelas->materis->pluck('file_path')->filter()->all());
        $kelas->delete();

        return response()->noContent();
    }

    /** POST /kelass/{id}/materis — file ≤10MB atau link eksternal. */
    public function storeMateri(Request $request, Kelas $kelas): JsonResponse
    {
        $this->pastikanBolehUnggahMateri($request, $kelas);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'in:file,link'],
            'link_url' => ['required_if:tipe,link', 'nullable', 'url'],
            'file' => ['required_if:tipe,file', 'nullable', 'file', 'max:10240'],
            'urutan' => ['sometimes', 'integer', 'min:1'],
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store("kelas/{$kelas->id}", 'public');
        }

        $materi = Materi::query()->create([
            'kelas_id' => $kelas->id,
            'judul' => $data['judul'],
            'tipe' => $data['tipe'],
            'file_path' => $filePath,
            'link_url' => $data['link_url'] ?? null,
            'urutan' => $data['urutan'] ?? $kelas->materis()->count() + 1,
        ]);

        return response()->json($this->materiResource($materi), 201);
    }

    /** PUT /materis/{id} */
    public function updateMateri(Request $request, Materi $materi): JsonResponse
    {
        $this->pastikanBolehUnggahMateri($request, $materi->kelas);

        $materi->update($request->validate([
            'judul' => ['sometimes', 'string', 'max:255'],
            'link_url' => ['sometimes', 'nullable', 'url'],
            'urutan' => ['sometimes', 'integer', 'min:1'],
        ]));

        return response()->json($this->materiResource($materi));
    }

    /** DELETE /materis/{id} */
    public function destroyMateri(Request $request, Materi $materi): Response
    {
        $this->pastikanBolehUnggahMateri($request, $materi->kelas);

        if ($materi->file_path !== null) {
            Storage::disk('public')->delete($materi->file_path);
        }

        $materi->delete();

        return response()->noContent();
    }

    private function resolveKomunitas(Request $request): ?string
    {
        $diminta = $request->filled('komunitas') ? strtoupper($request->string('komunitas')) : null;

        if (app(RoleResolver::class)->isAdminPusat($request->user())) {
            return $diminta;
        }

        $dikelola = app(RoleResolver::class)->komunitasDikelola($request->user());

        if ($dikelola->isEmpty()) {
            return $diminta;
        }

        if ($diminta !== null && ! $dikelola->contains($diminta)) {
            throw Problem::forbidden('Kelas komunitas lain bukan wewenangmu.');
        }

        return $diminta ?? $dikelola->first();
    }

    private function pastikanBolehKelola(Request $request, Kelas $kelas): void
    {
        if (app(RoleResolver::class)->isAdminPusat($request->user())) {
            return;
        }

        Gate::authorize('pengurus-komunitas', $kelas->komunitas?->kode ?? '');
    }

    private function pastikanBolehUnggahMateri(Request $request, Kelas $kelas): void
    {
        $user = $request->user();

        if (app(RoleResolver::class)->isAdminPusat($user)) {
            return;
        }

        if (Gate::forUser($user)->allows('pengurus-komunitas', $kelas->komunitas?->kode ?? '')) {
            return;
        }

        $sebagaiPengajar = $user->member?->penugasans()
            ->whereHas('periode', fn ($q) => $q->where('status', 'aktif'))
            ->whereHas('jabatan', fn ($q) => $q->where('divisi_id', $kelas->divisi_id))
            ->exists() ?? false;

        if (! $sebagaiPengajar) {
            throw Problem::forbidden('Hanya pengurus komunitas atau pengajar kelas terkait.');
        }
    }

    private function memberDisetujui($user, Kelas $kelas): bool
    {
        return $user->member?->keanggotaanKomunitas()
            ->where('komunitas_id', $kelas->komunitas_id)
            ->where('status', 'disetujui')
            ->exists() ?? false;
    }

    private function pengajar(Kelas $kelas): array
    {
        if ($kelas->divisi_id === null) {
            return [];
        }

        return Divisi::query()->find($kelas->divisi_id)?->jabatans()
            ->with('penugasans.member:id,nim,nama,foto_path')
            ->get()
            ->flatMap(fn ($j) => $j->penugasans)
            ->filter(fn ($p) => $p->member !== null)
            ->unique('member_id')
            ->map(fn ($p) => [
                'nim' => $p->member->nim,
                'nama' => $p->member->nama,
                'foto_path' => $p->member->foto_path,
                'jabatan' => $p->jabatan->nama,
            ])
            ->values()
            ->all() ?? [];
    }

    private function validated(Request $request, ?Kelas $existing = null): array
    {
        $kode = $this->resolveKomunitas($request);

        $data = $request->validate([
            'nama' => [$existing === null ? 'required' : 'sometimes', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'divisi_id' => ['nullable', Rule::exists('divisis', 'id')],
            'jadwal_hari' => ['nullable', 'string', 'max:20'],
            'jadwal_jam' => ['nullable', 'string', 'max:20'],
            'tempat' => ['nullable', 'string', 'max:255'],
        ]);

        return [$data, $kode];
    }

    private function resource(Kelas $k): array
    {
        return [
            'id' => $k->id,
            'nama' => $k->nama,
            'deskripsi' => $k->deskripsi,
            'divisi_id' => $k->divisi_id,
            'komunitas' => $k->komunitas ? [
                'id' => $k->komunitas->id,
                'kode' => $k->komunitas->kode,
            ] : null,
            'jadwal_hari' => $k->jadwal_hari,
            'jadwal_jam' => $k->jadwal_jam,
            'tempat' => $k->tempat,
        ];
    }

    private function materiResource(Materi $m): array
    {
        return [
            'id' => $m->id,
            'kelas_id' => $m->kelas_id,
            'judul' => $m->judul,
            'tipe' => $m->tipe,
            'file_path' => $m->file_path,
            'link_url' => $m->link_url,
            'urutan' => $m->urutan,
            'created_at' => $m->created_at?->toISOString(),
        ];
    }
}
