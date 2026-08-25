<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArtikelResource;
use App\Models\Artikel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArtikelController extends Controller
{
    /** GET /artikels — termasuk draft; filter status/kategori/q. */
    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return ArtikelResource::collection(
            Artikel::query()
                ->with('penulis:id,name')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->string('kategori')))
                ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                    $term = '%'.$request->string('q').'%';
                    $w->where('judul', 'like', $term)->orWhere('konten', 'like', $term);
                }))
                ->orderByDesc('created_at')
                ->paginate($perPage)
        );
    }

    /** POST /artikels — multipart cover opsional ≤5MB. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'konten' => ['required', 'string'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string', 'max:50'],
            'status' => ['sometimes', Rule::in(['draft', 'published'])],
            'cover' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('artikels/covers', 'public');
        }

        $status = $data['status'] ?? 'draft';

        return response()->json(ArtikelResource::make(Artikel::query()->create([
            ...collect($data)->except(['cover'])->all(),
            'user_id' => $request->user()->id,
            'slug' => $this->slugUnik($data['judul']),
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ]))->resolve(), 201);
    }

    /** GET /artikels/{id} */
    public function show(Artikel $artikel)
    {
        return response()->json(ArtikelResource::make($artikel->load('penulis:id,name'))->resolve());
    }

    /** PUT /artikels/{id} */
    public function update(Request $request, Artikel $artikel)
    {
        $data = $request->validate([
            'judul' => ['sometimes', 'string', 'max:255'],
            'konten' => ['sometimes', 'string'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'tags' => ['sometimes', 'array'],
            'status' => ['sometimes', Rule::in(['draft', 'published'])],
        ]);

        if (($data['status'] ?? null) === 'published') {
            $data['published_at'] ??= now();
        }
        if (($data['status'] ?? null) === 'draft') {
            $data['published_at'] = null;
        }

        $artikel->update($data);

        return response()->json(ArtikelResource::make($artikel)->resolve());
    }

    /** DELETE /artikels/{id} */
    public function destroy(Artikel $artikel): Response
    {
        if ($artikel->cover_path !== null) {
            Storage::disk('public')->delete($artikel->cover_path);
        }

        $artikel->delete();

        return response()->noContent();
    }

    private function slugUnik(string $judul): string
    {
        $dasar = Str::slug($judul);
        $slug = $dasar;
        $i = 2;

        while (Artikel::query()->where('slug', $slug)->exists()) {
            $slug = $dasar.'-'.($i++);
        }

        return $slug;
    }
}
