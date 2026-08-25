# Wireframe — Artikel List `/artikel` & Detail `/artikel/[slug]` Landing Page

> Status: Draft v1 · Visual: Engineering Blueprint · Render: ISR (list) · Dynamic/ISR (detail)

---

## 1. Layout ASCII

### `/artikel` — Daftar

```
┌──────────────────────────────────────────────────────────────┐
│ BERITA & PUBLIKASI                                           │
│ Kabar terbaru dari HIMSI UMKU.                               │
│ Filter: [Semua] [Kegiatan] [Prestasi] [Opini]  🔍 Cari…      │
├──────────────────────────────────────────────────────────────┐
│ ┌──────────────────────────┬─────────────────────────────┐   │
│ │                          │ KEGIATAN · 20 Agu           │   │
│ │      cover featured      │ Judul artikel terbaru,      │   │
│ │      (artikel paling     │ bisa dua-tiga baris di sini │   │
│ │      baru — besar)       │ Excerpt dua baris…          │ │
│ │                          │ ✍ Nama Penulis              │   │
│ └──────────────────────────┴─────────────────────────────┘   │
│                                                              │
│ ┌────────┐ ┌────────┐ ┌────────┐                             │
│ │ cover  │ │ cover  │ │ cover  │                             │
│ │ judul  │ │ judul  │ │ judul  │   ← grid kartu standar      │
│ │ meta   │ │ meta   │ │ meta   │                             │
│ └────────┘ └────────┘ └────────┘                             │
│                  pagination                                  │
└──────────────────────────────────────────────────────────────┘
```

### `/artikel/[slug]` — Detail

```
┌──────────────────────────────────────────────────────────────┐
│ ← Semua berita                              [⤴ Share] [𝕏][📷]│
├──────────────────────────────────────────────────────────────┤
│         KATEGORI · 20 AGUSTUS 2026 · 5 MENIT BACA            │
│                                                              │
│    JUDUL ARTIKEL BESAR SERIF-DISPLAY                         │
│    Dua Baris Maksimal                                        │
│                                                              │
│    ✍ oleh Nama Penulis · avatar                              │
│                                                              │
│    ┌────────────────────────────────────────────────┐        │
│    │            COVER / GAMBAR UTAMA                │        │
│    │        caption mono kecil di bawah             │        │
│    └────────────────────────────────────────────────┘        │
│                                                              │
│    Isi artikel rich-text:                                    │
│    Paragraf nyaman dibaca, max-width 65ch, line-height       │
│    1.75. Sub-heading H2/H3 berjenjang.                       │
│                                                              │
│    • List item                                               │
│    • List item                                               │
│                                                              │
│    > Blockquote bergaya kutipan                              │
│                                                              │
│    Gambar inline dengan caption                              │
│                                                              │
│ ── tag: #workshop #iot                                       │
│ ──────────────────────────────────────────────               │
│ ARTIKEL TERKAIT (3 kartu kecil)                              │
└──────────────────────────────────────────────────────────────┘
```

## 2. Component Tree

```
<ArtikelListPage>
├── <FilterBar>                  kategori chips + search input
├── <FeaturedCard>               artikel published terbaru
├── <ArtikelGrid> + <Pagination>
<ArtikelDetailPage generateMetadata>
├── <Breadcrumb>                 ← Semua berita
├── <ArtikelHeader>              kategori · tanggal · estimasi baca · penulis
├── <Prose>                      rich-text render (typography plugin)
├── <TagRow>
├── <ShareButtons>               native share API + fallback copy link
└── <RelatedArticles>
```

## 3. State Matrix

| Kondisi | List | Detail |
|---------|------|--------|
| Loading | skeleton kartu ×6 | skeleton header+prose lines |
| Empty hasil filter | "Belum ada artikel kategori ini" + reset | — |
| Slug tidak ada | — | 404 custom + tombol semua berita |
| Draft diakses langsung | — | 404 (draft tak pernah tampil publik) |
| Gambar gagal | placeholder abu + alt text | sama |

## 4. UX Notes

- Estimasi menit baca = word count / 200 — sinyal kematangan konten
- Share pakai `navigator.share` di mobile; desktop fallback salin URL
- Progress bar baca tipis cobalt di atas viewport saat scroll artikel (opsional fase 2)
- OG image otomatis dari cover + judul (ADR L4) — preview IG/WA rapi
- Related articles = kategori sama, published terbaru, exclude diri sendiri

## 5. Responsive

| Breakpoint | List | Detail |
|-----------|------|--------|
| `<640px` | stack vertikal | prose padding 20px; font 17px |
| `≥768px` | grid 2-3 kolom | max-w-3xl center; sidebar none |

---
*Terintegrasi:* PRD landing §3 · openapi `/publik/artikels`
