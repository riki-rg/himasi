<?php

namespace App\Http\Controllers;

use App\Http\Resources\GaleriAlbumResource;
use App\Http\Resources\GaleriFotoResource;
use App\Models\GaleriAlbum;
use App\Models\GaleriFoto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class GaleriController extends Controller
{
    /** GET /galeri/albums — termasuk album tanpa foto; filter event_id. */
    public function albums(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return GaleriAlbumResource::collection(
            GaleriAlbum::query()
                ->when($request->filled('event_id'), fn ($q) => $q->where('event_id', $request->integer('event_id')))
                ->withCount('fotos')
                ->orderByDesc('created_at')
                ->paginate($perPage)
        );
    }

    /** POST /galeri/albums */
    public function storeAlbum(Request $request)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'event_id' => ['nullable', 'exists:events,id'],
        ]);

        return response()->json(GaleriAlbumResource::make(GaleriAlbum::query()->create($data))->resolve(), 201);
    }

    /** PUT /galeri/albums/{id} */
    public function updateAlbum(Request $request, GaleriAlbum $album)
    {
        $data = $request->validate([
            'judul' => ['sometimes', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'event_id' => ['nullable', 'exists:events,id'],
        ]);

        $album->update($data);

        return response()->json(GaleriAlbumResource::make($album->loadCount('fotos'))->resolve());
    }

    /** DELETE /galeri/albums/{id} — cascade fotonya (ADR D5 via Storage). */
    public function destroyAlbum(GaleriAlbum $album): Response
    {
        Storage::disk('public')->delete($album->fotos->pluck('path')->all());

        if ($album->cover_path !== null) {
            Storage::disk('public')->delete($album->cover_path);
        }

        $album->delete();

        return response()->noContent();
    }

    /** POST /galeri/albums/{id}/fotos — multi-upload ≤5MB per foto, max 30 file. */
    public function storeFotos(Request $request, GaleriAlbum $album)
    {
        $data = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:30'],
            'files.*' => ['required', 'image', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $mulai = $album->fotos()->count();
        $tersimpan = collect();

        foreach ($request->file('files') as $i => $file) {
            $tersimpan->push(GaleriFoto::query()->create([
                'album_id' => $album->id,
                'path' => $file->store("galeri/{$album->id}", 'public'),
                'caption' => $i === 0 ? ($data['caption'] ?? null) : null,
                'urutan' => $mulai + $i + 1,
            ]));
        }

        if ($album->cover_path === null && $tersimpan->isNotEmpty()) {
            $album->update(['cover_path' => $tersimpan->first()->path]);
        }

        return response()->json(
            collect($tersimpan)->map(fn ($f) => GaleriFotoResource::make($f)->resolve())->values(),
            201
        );
    }

    /** PUT /galeri/fotos/{id} — caption & urutan. */
    public function updateFoto(Request $request, GaleriFoto $foto)
    {
        $foto->update($request->validate([
            'caption' => ['nullable', 'string', 'max:255'],
            'urutan' => ['sometimes', 'integer', 'min:1'],
        ]));

        return response()->json(GaleriFotoResource::make($foto)->resolve());
    }

    /** DELETE /galeri/fotos/{id} */
    public function destroyFoto(GaleriFoto $foto): Response
    {
        Storage::disk('public')->delete($foto->path);
        $foto->delete();

        return response()->noContent();
    }
}
