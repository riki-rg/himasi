<?php

namespace App\Http\Controllers;

use App\Models\Komunitas;
use App\Models\Proyek;
use App\Services\RoleResolver;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProyekController extends Controller
{
    /** GET /proyeks — draft+published; pengurus otomatis di-scope komunitasnya (US-23). */
    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $kode = self::resolveKomunitas($request);

        $paginator = Proyek::query()
            ->when(
                $kode !== null,
                fn ($q) => $q->whereHas('komunitas', fn ($k) => $k->where('kode', $kode))
            )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $term = '%'.$request->string('q').'%';
                $w->where('judul', 'like', $term)->orWhere('deskripsi', 'like', $term);
            }))
            ->with(['pembuat:id,nim,nama', 'komunitas:id,kode'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => collect($paginator->items())->map(fn ($p) => $this->resource($p))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    /** POST /proyeks — thumbnail multipart ≤5MB; default draft. */
    public function store(Request $request)
    {
        [$data, $kode] = $this->validated($request);

        if (! $kode instanceof \BackedEnum && ! is_string($kode)) {
            throw Problem::validation(['komunitas_id' => ['Wajib menentukan komunitas.']]);
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_path'] = $request->file('thumbnail')->store('proyeks/thumbnails', 'public');
        }
        unset($data['thumbnail']);

        $status = $data['status'] ?? 'draft';

        return response()->json($this->resource(Proyek::query()->create([
            ...$data,
            'komunitas_id' => Komunitas::idByKode((string) $kode),
            'slug' => $this->slugUnik($data['judul']),
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ])), 201);
    }

    /** GET /proyeks/{id} */
    public function show(Request $request, Proyek $proyek): JsonResponse
    {
        $this->pastikanBolehKelola($request, $proyek);

        return response()->json($this->resource($proyek));
    }

    /** PUT /proyeks/{id} — publish/unpublish via status. */
    public function update(Request $request, Proyek $proyek)
    {
        $this->pastikanBolehKelola($request, $proyek);
        [$data] = $this->validated($request, $proyek);

        if (($data['status'] ?? null) === 'published') {
            $data['published_at'] ??= now();
        }
        if (($data['status'] ?? null) === 'draft') {
            $data['published_at'] = null;
        }

        $proyek->update($data);

        return response()->json($this->resource($proyek));
    }

    /** DELETE /proyeks/{id} */
    public function destroy(Request $request, Proyek $proyek): Response
    {
        $this->pastikanBolehKelola($request, $proyek);

        if ($proyek->thumbnail_path !== null) {
            Storage::disk('public')->delete($proyek->thumbnail_path);
        }

        $proyek->delete();

        return response()->noContent();
    }

    /**
     * Kode komunitas efektif: admin bebas lewat param; pengurus dipaksa
     * ke komunitasnya — minta komunitas lain → 403; bukan pengurus → 403.
     */
    private static function resolveKomunitas(Request $request): ?string
    {
        $diminta = $request->filled('komunitas')
            ? strtoupper($request->string('komunitas'))
            : null;

        if (app(RoleResolver::class)->isAdminPusat($request->user())) {
            return $diminta;
        }

        $dikelola = app(RoleResolver::class)->komunitasDikelola($request->user());

        if ($dikelola->isEmpty()) {
            throw Problem::forbidden('Hanya pengurus komunitas atau admin yang boleh mengelola karya.');
        }

        if ($diminta !== null && ! $dikelola->contains($diminta)) {
            throw Problem::forbidden('Karya komunitas lain bukan wewenangmu.');
        }

        return $diminta ?? $dikelola->first();
    }

    private function pastikanBolehKelola(Request $request, Proyek $proyek): void
    {
        if (app(RoleResolver::class)->isAdminPusat($request->user())) {
            return;
        }

        Gate::authorize('pengurus-komunitas', $proyek->komunitas?->kode ?? '');
    }

    /**
     * @return array{0: array<string, mixed>, 1: ?string}
     */
    private function validated(Request $request, ?Proyek $existing = null): array
    {
        $kode = self::resolveKomunitas($request);

        $data = $request->validate([
            'judul' => [$existing === null ? 'required' : 'sometimes', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'link_demo' => ['nullable', 'url'],
            'link_repo' => ['nullable', 'url'],
            'teknologi' => ['sometimes', 'array'],
            'teknologi.*' => ['string', 'max:50'],
            'pembuat_id' => [$existing === null ? 'required' : 'sometimes', Rule::exists('members', 'id')],
            'divisi_id' => ['nullable', Rule::exists('divisis', 'id')],
            'status' => ['sometimes', Rule::in(['draft', 'published'])],
            'thumbnail' => ['nullable', 'image', 'max:5120'],
        ]);

        return [$data, $kode];
    }

    private function slugUnik(string $judul): string
    {
        $dasar = Str::slug($judul);
        $slug = $dasar;
        $i = 2;

        while (Proyek::query()->where('slug', $slug)->exists()) {
            $slug = $dasar.'-'.($i++);
        }

        return $slug;
    }

    private function resource(Proyek $p): array
    {
        return [
            'id' => $p->id,
            'judul' => $p->judul,
            'slug' => $p->slug,
            'deskripsi' => $p->deskripsi,
            'thumbnail_path' => $p->thumbnail_path,
            'link_demo' => $p->link_demo,
            'link_repo' => $p->link_repo,
            'teknologi' => $p->teknologi ?? [],
            'pembuat' => $p->pembuat ? [
                'id' => $p->pembuat->id,
                'nim' => $p->pembuat->nim,
                'nama' => $p->pembuat->nama,
            ] : null,
            'divisi_id' => $p->divisi_id,
            'komunitas' => $p->komunitas ? [
                'id' => $p->komunitas->id,
                'kode' => $p->komunitas->kode,
            ] : null,
            'status' => $p->status,
            'published_at' => $p->published_at?->toISOString(),
            'created_at' => $p->created_at?->toISOString(),
        ];
    }
}
