# Wireframe — Galeri Album `/galeri/[album]` Landing Page

> Route: `/galeri/[album]` · Render: Dynamic/ISR · Status: Draft v1 · App: landing page HIMSI (Next.js)
> Visual: Engineering Blueprint (paper × cobalt × Plex)
> Fungsi: menampilkan dokumentasi kegiatan dalam satu album + pengalaman lihat foto yang nyaman (lightbox)

---

## 1. Layout ASCII

### Halaman Album

```
┌──────────────────────────────────────────────────────────────┐
│ ▣ HIMSI   Berita Agenda Struktur Galeri              🌙      │
├──────────────────────────────────────────────────────────────┤
│ ← Semua galeri                                               │
│                                                              │
│ WORKSHOP IOT DASAR                    (judul besar, display) │
│ 📅 15 Agu 2026 · 📍 Lab SI · 🖼 24 foto                      │
│ Dokumentasi sesi pertama rakit sensor suhu & kirim data      │
│ ke dashboard web.                                            │
│ ──────────────────────────────────────────── line cobalt     │
│                                                              │
│ ┌────────┐ ┌────────┐ ┌────────┐                            │
│ │        │ │        │ │        │   ← grid masonry           │
│ │  foto  │ │  foto  │ │  foto  │     (tinggi alami,         │
│ ├────────┤ ├────────┤ ├────────┤      bukan crop paksa)     │
│ │  foto  │ │  foto  │ │        │                            │
│ │ (wide) │ │        │ ├────────┤   hover: zoom halus +      │
│ ├────────┤ ├────────┤ │  foto  │   overlay caption          │
│ │  foto  │ │  foto  │ │        │                            │
│ └────────┘ └────────┘ └────────┘                            │
│                  … dst sampai 24 foto …                       │
│                                                              │
│ ALBUM LAINNYA                                                │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐                      │
│ │ Seminar  │ │ Lomba    │ │ Kopdar   │  ← cover+judul+jumlah │
│ │ Karier   │ │ UI/UX    │ │ BitSI    │    kartu → album lain │
│ └──────────┘ └──────────┘ └──────────┘                      │
└──────────────────────────────────────────────────────────────┘
```

### Lightbox (overlay saat foto diklik)

```
╔══════════════════════════════════════════════════════════╗
║  ✕                                    3 / 24        ⤴    ║
║                                                          ║
║   ‹                                                      ║
║            ┌─────────────────────────┐                   ║
║            │                         │                   ║
║            │      FOTO FULLSCREEN    │                   ║
║            │      (object-contain)   │                   ║
║            │                         │                   ║
║            └─────────────────────────┘                   ║
║                                                      ›   ║
║  Caption foto jika ada — mono kecil, bawah tengah        ║
║                                                          ║
╚══════════════════════════════════════════════════════════╝
  backdrop hitam 90% · klik area kosong = tutup
```

## 2. Component Tree

```
<AlbumPage route="/galeri/[album]" generateMetadata>
├── <Navbar>
├── <AlbumHeader>                judul · meta (tanggal, lokasi, jumlah) · deskripsi
├── <PhotoGrid>
│   └── <PhotoTile[]>            next/image + blurDataURL + caption hover
├── <Lightbox>                   state global halaman (index aktif)
│   ├── <LightboxImage>          object-contain, preload tetangga ±1
│   ├── <NavArrows> ‹ ›          + keyboard ← → · swipe gesture mobile
│   ├── <CounterBadge>           "3 / 24"
│   └── <ShareButton> ⤴          copy deep-link `?foto=3` ke clipboard
└── <RelatedAlbums>              Card[] ×3 dari event/komunitas sama
```

## 3. State Matrix

| Kondisi | Perilaku |
|---------|----------|
| Loading halaman | Skeleton grid mengikuti rasio acak realistis (bukan kotak seragam) |
| Loading tiap foto | Blur placeholder dari thumbnail kecil; fade-in saat siap |
| Foto gagal load | Placeholder ikon 🖼 + label "gagal dimuat", grid tidak rusak layoutnya |
| Album kosong | Redirect balik ke `/galeri` dengan toast "Album ini kosong" |
| Album tidak ada (404) | Halaman 404 custom dengan tombol ke galeri |
| Lightbox dibuka | Scroll body dikunci; fokus trap di overlay; ESC tutup |
| Deep-link `?foto=3` | Langsung buka lightbox di foto tsb — bagikan foto spesifik via URL |

## 4. Catatan UX Detail

- **Masonry, bukan crop** — foto landscape & portrait tampil utuh proporsinya; dokumentasi jujur, tidak dipotong asal rapi
- Preload foto tetangga (index ±1) saat lightbox terbuka → navigasi panah terasa instan
- Swipe kiri/kanan di mobile + panah keyboard di desktop; indikator titik posisi opsional untuk album pendek
- Tombol share menyalin URL `…?foto=3` — penerima langsung dibuka di foto itu (deep-linkable)
- Caption foto hanya muncul di lightbox & hover — grid tetap bersih
- SEO: tiap foto punya alt text = caption; metadata album dinamis untuk OG preview

> 💡 Learning: Fitur kecil `?foto=3` adalah contoh *URL as state* — halaman jadi bisa dibagikan per-momen tanpa backend tambahan. Pola ini gratis di Next.js App Router karena query param tersedia di server component.

## 5. Responsive Notes

| Breakpoint | Grid | Lightbox |
|-----------|------|----------|
| `<640px` | 2 kolom rapat; swipe antar foto; tap ganda = zoom | nav via swipe; ✕ pojok kanan atas |
| `640–1023px` | 3 kolom | panah samping muncul |
| `≥1024px` | 4 kolom masonry | panah besar hover; keyboard hint |

---
*Terintegrasi:* PRD `landing-page.md` §3 (route) · ADR L3 (next/image) · wireframe `home.md`
