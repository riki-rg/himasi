<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArtikelResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\GaleriAlbumResource;
use App\Http\Resources\PengumumanResource;
use App\Models\Artikel;
use App\Models\Divisi;
use App\Models\Event;
use App\Models\GaleriAlbum;
use App\Models\Kelas;
use App\Models\Pengumuman;
use App\Models\Proyek;
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

    /** GET /publik/proyeks — karya published per komunitas + nama pembuat (US-23). */
    public function proyeks(Request $request)
    {
        $paginator = Proyek::query()
            ->where('status', 'published')
            ->when($request->filled('komunitas'), fn ($q) => $q->whereHas(
                'komunitas',
                fn ($k) => $k->where('kode', $request->string('komunitas')->upper())
            ))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $term = '%'.$request->string('q').'%';
                $w->where('judul', 'like', $term)->orWhere('deskripsi', 'like', $term);
            }))
            ->with(['pembuat:id,nim,nama,foto_path', 'komunitas:id,kode,nama'])
            ->orderByDesc('published_at')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100));

        return response()->json([
            'data' => collect($paginator->items())->map(fn ($p) => [
                'id' => $p->id,
                'judul' => $p->judul,
                'slug' => $p->slug,
                'deskripsi' => $p->deskripsi,
                'thumbnail_path' => $p->thumbnail_path,
                'link_demo' => $p->link_demo,
                'link_repo' => $p->link_repo,
                'teknologi' => $p->teknologi ?? [],
                'pembuat' => $p->pembuat ? [
                    'nim' => $p->pembuat->nim,
                    'nama' => $p->pembuat->nama,
                    'foto_path' => $p->pembuat->foto_path,
                ] : null,
                'komunitas' => $p->komunitas?->kode,
                'published_at' => $p->published_at?->toISOString(),
            ])->values(),
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

    /** GET /publik/proyeks/{slug} */
    public function proyekDetail(string $slug)
    {
        $proyek = Proyek::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with(['pembuat:id,nim,nama,foto_path,link_portofolio,link_instagram'])
            ->first();

        if ($proyek === null) {
            throw Problem::notFound('Karya tidak ditemukan.');
        }

        return response()->json([
            'id' => $proyek->id,
            'judul' => $proyek->judul,
            'slug' => $proyek->slug,
            'deskripsi' => $proyek->deskripsi,
            'thumbnail_path' => $proyek->thumbnail_path,
            'link_demo' => $proyek->link_demo,
            'link_repo' => $proyek->link_repo,
            'teknologi' => $proyek->teknologi ?? [],
            'pembuat' => $proyek->pembuat ? [
                'nim' => $proyek->pembuat->nim,
                'nama' => $proyek->pembuat->nama,
                'foto_path' => $proyek->pembuat->foto_path,
                'link_portofolio' => $proyek->pembuat->link_portofolio,
                'link_instagram' => $proyek->pembuat->link_instagram,
            ] : null,
            'published_at' => $proyek->published_at?->toISOString(),
        ]);
    }

    /** GET /publik/kelass — daftar kelas TANPA materi (US-24). */
    public function kelass(Request $request)
    {
        return response()->json(Kelas::query()
            ->when($request->filled('komunitas'), fn ($q) => $q->whereHas(
                'komunitas',
                fn ($k) => $k->where('kode', $request->string('komunitas')->upper())
            ))
            ->when($request->filled('q'), fn ($q) => $q->where('nama', 'like', '%'.$request->string('q').'%'))
            ->with('divisi:id,nama')
            ->get()
            ->map(fn ($k) => [
                'id' => $k->id,
                'nama' => $k->nama,
                'deskripsi' => $k->deskripsi,
                'divisi' => $k->divisi?->nama,
                'jadwal_hari' => $k->jadwal_hari,
                'jadwal_jam' => $k->jadwal_jam,
                'tempat' => $k->tempat,
                'pengajar' => $this->pengajarRingkas($k),
            ])->values());
    }

    private function pengajarRingkas(Kelas $kelas): array
    {
        if ($kelas->divisi_id === null) {
            return [];
        }

        return Divisi::query()->find($kelas->divisi_id)?->jabatans()
            ->with('penugasans.member:id,nim,nama')
            ->get()
            ->flatMap(fn ($j) => $j->penugasans)
            ->filter(fn ($p) => $p->member !== null)
            ->unique('member_id')
            ->map(fn ($p) => ['nama' => $p->member->nama])
            ->values()->all() ?? [];
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
