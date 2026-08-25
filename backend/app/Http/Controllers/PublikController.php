<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArtikelResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\GaleriAlbumResource;
use App\Http\Resources\PengumumanResource;
use App\Models\Artikel;
use App\Models\Event;
use App\Models\GaleriAlbum;
use App\Models\Pengumuman;
use App\Support\Problem;
use Illuminate\Http\Request;

class PublikController extends Controller
{
    /** GET /publik/artikels — hanya published. */
    public function artikels(Request $request)
    {
        return ArtikelResource::collection($this->artikelQuery($request)->paginate(
            min(max($request->integer('per_page', 15), 1), 100)
        ));
    }

    /** GET /publik/artikels/{slug} — draft tidak pernah tampil publik (US-10). */
    public function artikelDetail(string $slug)
    {
        $artikel = $this->artikelQuery()->where('slug', $slug)->first();

        if ($artikel === null) {
            throw Problem::notFound('Artikel tidak ditemukan.');
        }

        return response()->json(ArtikelResource::make($artikel)->resolve());
    }

    /** GET /publik/events — published; ?mendatang=true; ?komunitas=KODE. */
    public function events(Request $request)
    {
        return EventResource::collection(Event::query()
            ->where('status', 'published')
            ->when(
                $request->boolean('mendatang'),
                fn ($q) => $q->where('mulai', '>=', now())
            )
            ->when($request->filled('komunitas'), fn ($q) => $q->whereHas(
                'komunitas',
                fn ($k) => $k->where('kode', $request->string('komunitas')->upper())
            ))
            ->orderBy('mulai')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
        );
    }

    /** GET /publik/events/{id} */
    public function eventDetail(int $id)
    {
        $event = Event::query()->where('status', 'published')->find($id);

        if ($event === null) {
            throw Problem::notFound('Event tidak ditemukan.');
        }

        return response()->json(EventResource::make($event)->resolve());
    }

    /** GET /publik/galeri/albums */
    public function albums(Request $request)
    {
        return GaleriAlbumResource::collection(GaleriAlbum::query()
            ->when($request->filled('event_id'), fn ($q) => $q->where('event_id', $request->integer('event_id')))
            ->withCount('fotos')
            ->orderByDesc('created_at')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
        );
    }

    /** GET /publik/galeri/albums/{id} — detail + semua foto terurut. */
    public function albumDetail(GaleriAlbum $album)
    {
        return response()->json(GaleriAlbumResource::make($album->load(['fotos' => fn ($q) => $q->orderBy('urutan')]))->resolve());
    }

    /** GET /publik/pengumumans — masa tayang aktif; prioritas penting duluan (US-12). */
    public function pengumumans(Request $request)
    {
        return response()->json(PengumumanResource::collection(Pengumuman::query()
            ->where(fn ($q) => $q->whereNull('tayang_mulai')->orWhere('tayang_mulai', '<=', today()))
            ->where(fn ($q) => $q->whereNull('tayang_selesai')->orWhere('tayang_selesai', '>=', today()))
            ->when($request->filled('komunitas'), fn ($q) => $q->whereHas(
                'komunitas',
                fn ($k) => $k->where('kode', $request->string('komunitas')->upper())
            ))
            ->orderByRaw("CASE WHEN prioritas = 'penting' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get()
        )->resolve());
    }

    private function artikelQuery(?Request $request = null)
    {
        return Artikel::query()
            ->where('status', 'published')
            ->when($request?->filled('kategori'), fn ($q) => $q->where('kategori', $request->string('kategori')))
            ->when($request?->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $term = '%'.$request->string('q').'%';
                $w->where('judul', 'like', $term)->orWhere('konten', 'like', $term);
            }))
            ->orderByDesc('published_at')
            ->with('penulis:id,name');
    }
}
