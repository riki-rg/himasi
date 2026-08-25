# Wireframe — Home Landing Page HIMSI UMKU

> Route `/` · Render: Static + ISR 60s · Prinsip: mobile-first, section stacking

## 1. Layout ASCII

```
┌─────────────────────────────────────────────────┐
│ ▣ LOGO HIMSI   Berita Agenda Struktur Galeri  🌙│ ← navbar sticky,
├─────────────────────────────────────────────────┤   blur backdrop
│                                                 │
│        HIMPUNAN MAHASISWA                       │
│        SISTEM INFORMASI                         │
│        UNIVERSITAS MUHAMMADIYAH KUDUS           │
│                                                 │
│   "Satu Himpunan, Banyak Wawasan"               │
│                                                 │
│   [ Lihat Program ]  [ Gabung Komunitas ]       │ ← CTA utama
│                                                 │
│         [ ilustrasi/foto kegiatan ]             │
├─────────────────────────────────────────────────┤
│ ⚠ PENGUMUMAN PENTING: Workshop IoT daftar s/d.. │ ← marquee/banner,
├─────────────────────────────────────────────────┤   hanya jika ada
│  TENTANG KAMI                                   │
│  ┌────────────┐  Paragraf singkat profil        │
│  │ foto hima  │  himpunan...                    │
│  └────────────┘                                 │
│  ✦ Visi    ✦ Misi (accordion/list ringkas)     │
├─────────────────────────────────────────────────┤
│  HIMSI DALAM ANGKA                              │
│  [150+ Anggota] [3 Komunitas] [20 Event/Tahun]  │ ← stat counter animasi
├─────────────────────────────────────────────────┤
│  KOMUNITAS KAMI                                 │
│  ┌──────────────┐  ┌──────────────┐             │
│  │ ⚡ BitSI      │  │ 📚 Sibiner    │            │
│  │ Web dev·IoT  │  │ Baca buku &   │           │
│  │ Jaringan·srv │  │ literasi      │           │
│  │ [Masuk App→] │  │ [Masuk App→]  │           │ ← card gradient,
│  └──────────────┘  └──────────────┘             │   hover lift
├─────────────────────────────────────────────────┤
│  STRUKTUR ORGANISASI          [Lihat semua →]   │
│  Tab: [HIMSI] [BitSI] [Sibiner]                 │
│  ┌───────────────────────────────────┐          │
│  │      [Foto Ketua Umum]            │          │
│  │       Nama Ketua Umum             │          │
│  │  ─────────────────────            │          │
│  │  [Sekretaris]  [Bendahara]        │          │
│  │  ─────────────────────            │          │
│  │  [Divisi IT] [Divisi Medinfo] ... │          │ ← grid jabatan
│  └───────────────────────────────────┘          │
├─────────────────────────────────────────────────┤
│  BERITA TERBARU               [Semua berita →]  │
│  ┌────────┐ ┌────────┐ ┌────────┐               │
│  │ cover  │ │ cover  │ │ cover  │              │ ← 3 kartu,
│  │ judul  │ │ judul  │ │ judul  │               │   horizontal scroll
│  │ tanggal│ │ tanggal│ │ tanggal│               │   di mobile
│  └────────┘ └────────┘ └────────┘               │
├─────────────────────────────────────────────────┤
│  AGENDA MENDATANG             [Semua agenda →]  │
│  📅 12 Sep — Seminar Karier SI    @ Auditorium  │
│  📅 20 Sep — Lomba UI/UX          @ Online      │ ← list rows,
│  📅 28 Sep — Kopdar BitSI #12     @ Lab SI      │   icon kalender
├─────────────────────────────────────────────────┤
│  GALERI                       [Semua galeri →]  │
│  ┌────┐┌────┐┌────┐┌────┐                       │
│  │foto││foto││foto││foto│                      │ ← masonry/grid,
│  └────┘└────┘└────┘└────┘                       │   hover zoom
├─────────────────────────────────────────────────┤
│ ▣ LOGO    Navigasi   Kontak & Sosmed            │
│           · Berita    ✉ email@himsi             │
│  © 2026 HIMSI UMKU        📷 @himsiumku         │
│  Universitas Muhammadiyah Kudus                 │
└─────────────────────────────────────────────────┘
```

## 2. Component Tree

```
<RootLayout>                    (font, theme provider, metadata)
└── <HomePage>
    ├── <Navbar>                sticky · active-link · mobile hamburger
    ├── <HeroSection>           headline · CTA×2 · background image
    ├── <PengumumanBanner>      kondisional: hanya jika ada prioritas=penting
    ├── <TentangSection>        foto · visi-misi accordion
    ├── <StatCounterSection>    angka animasi count-up
    ├── <KomunitasCards>        2× Card(BitSI|Sibiner) → external link app
    ├── <StrukturPreview>       Tabs(komunitas) → OrgChart(preview level atas)
    │   └── <OrgChartNode>      avatar+nama+jabatan, recursive
    ├── <ArtikelTerbaru>        Card[] ×3 → /artikel/[slug]
    ├── <AgendaMendatang>       EventRow[] ×3–5 → /agenda
    ├── <GaleriPilihan>         PhotoGrid ×8 → /galeri
    ├── <Footer>                nav · kontak · sosmed · kredit
    └── <Toaster/>              (opsional feedback)
```

## 3. State Matrix (per section data-driven)

| Section | Loading | Empty | Error/API down | Success |
|---------|---------|-------|----------------|---------|
| PengumumanBanner | skip render (ISR) | skip render | skip render | banner amber dismissible |
| StrukturPreview | Skeleton avatar blocks | "Struktur segera diperbarui" | fallback teks statis + retry | tree avatar+nama |
| ArtikelTerbaru | Skeleton card ×3 | Section disembunyikan | Section disembunyikan | 3 kartu cover+judul+tanggal |
| AgendaMendatang | Skeleton row ×3 | "Belum ada agenda — pantau IG kami" | disembunyikan | row tanggal+judul+lokasi |
| GaleriPilihan | Blur placeholder | disembunyikan | disembunyikan | grid 8 foto |

> Aturan global (ADR L7): API down tidak boleh bikin halaman blank — tiap section degrade secara mandiri.

## 4. Responsive Notes (mobile-first)

| Breakpoint | Perilaku |
|------------|----------|
| `< 640px` (mobile) | Navbar → hamburger fullscreen; komunitas cards stack; artikel horizontal-scroll snap; struktur → list vertikal per divisi (bukan tree); galeri grid 2 kolom |
| `≥ 768px` (tablet) | Grid 2 kolom artikel; struktur mulai tree layout |
| `≥ 1024px` (desktop) | Semua grid penuh sesuai wireframe; max-w-7xl centered |

## 5. Catatan UX Detail

- Dark mode toggle (class strategy, persist localStorage) — default mengikuti sistem
- Scroll reveal animasi halus (fade-up sekali, hormati `prefers-reduced-motion`)
- Semua gambar eksternal via `next/image` + `sizes` benar (L3)
- Link aplikasi BitSI/Sibiner = konstanta env `NEXT_PUBLIC_BITSI_URL`, `NEXT_PUBLIC_SIBINER_URL` (mudah diisi pas app-nya jadi)
- **CTA "Gabung Komunitas" mengarah ke app BitSI** — landing page TIDAK punya form registrasi (keputusan OQ-3; registrasi member jadi fitur aplikasi BitSI)

---
*Halaman detail (`/artikel/[slug]`, `/galeri/[album]`, `/struktur`) mengikuti pattern sama: skeleton → content → empty/error ramah.*
