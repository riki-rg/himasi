# Wireframe — Home Publik App Sibiner `/`

> Route: `/` · Render: Static + ISR · Status: Draft v1
> Fungsi: memperkenalkan Sibiner ke calon anggota HIMSI & menampilkan koleksi review buku sebagai konten pembeda
> ⚠️ Usulan identitas visual — **"Warm Library"**: kertas krem `#faf6ee` × hijau hutan tua `#1e3a2f` × aksen amber · font display serif **Cormorant Garamond + Libre Baskerville** (pairing "Editorial Classic" — hasil riset skill ui-ux-pro-max) untuk nuansa literasi · tetap share komponen struktural dengan `packages/ui`. *Bukan putih×biru seperti BitSI — tiap komunitas punya rasa, satu keluarga tipografi dasar.*

---

## 1. Layout ASCII

### Desktop (≥1024px)

```
┌──────────────────────────────────────────────────────────────────┐
│ ▣ SIBINER   Rak Buku  Diskusi  Pengurus        [Masuk]  [Gabung]│ ← navbar, logo
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│         ── garis dekoratif buku terbuka ──                       │
│   SISTEM INFORMASI BICARA NALAR                                  │
│   DAN LITERASI                        (serif italic besar)       │
│                                                                  │
│   Komunitas baca-baca bareng anggota SI UMKU.                    │
│   Dari novel sampai buku nonfiksi — dibahas,                     │
│   diperdebatkan, dirangkum bareng.                               │
│                                                                  │
│   [ Lihat Rak Buku ]   [ Gabung Sibiner ]                        │
│                                                                  │
│              📚 ilustrasi tumpukan buku / foto                   │
├──────────────────────────────────────────────────────────────────┤
│  RAK BUKU KAMI                            [Lihat semua →]       │
│  ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐                    │
│  │ [cover]│ │ [cover]│ │ [cover]│ │ [cover]│                    │
│  │        │ │        │ │        │ │        │  ← hover: card      │
│  │ Judul  │ │ Judul  │ │ Judul  │ │ Judul  │    terangkat +      │
│  │ ★4.5   │ │ ★5.0   │ │ ★4.0   │ │ ★4.8   │    quote muncul     │
│  │ —Alya  │ │ —Bima  │ │ —Citra │ │ —Dimas │                    │
│  └────────┘ └────────┘ └────────┘ └────────┘                    │
│  filter chips: [Semua] [Fiksi] [Nonfiksi] [Filosofi]            │
├──────────────────────────────────────────────────────────────────┤
│  DISKUSI RUTIN                                                   │
│  📖 Sabtu malam · 19.30 · ruang perpustakaan/daring              │
│  Format: satu buku per dua minggu — bedah bab per sesi           │
│  [Jadwal lengkap →]                                              │
├──────────────────────────────────────────────────────────────────┤
│  DI BALIK SIBINER                          (struktur organisasi) │
│  ┌──────────────┐  ┌──────────────────────────┐                  │
│  │  [Foto Ketua] │  │ Ketua Divisi   : Nama    │                 │
│  │  Divisi       │  │ Sekretaris     : Nama    │                 │
│  │  Organisasi   │  │ Anggota        : N, N, N │                 │
│  └──────────────┘  └──────────────────────────┘                  │
├──────────────────────────────────────────────────────────────────┤
│  GALERI                        3-6 foto dokumentasi diskusi      │
├──────────────────────────────────────────────────────────────────┤
│  Kata mereka:                                                    │
│  ❝ Baru sadar debat buku bisa seru begini ❞ — Anggota 2023      │
├──────────────────────────────────────────────────────────────────┤
│  Mau ikutan?                                                     │
│  Sibiner khusus anggota HIMSI aktif — hubungi kami:              │
│  [ DM Instagram @sibiner.umku ]  [ WhatsApp Ketua ]              │
│  © Sibiner · Divisi Organisasi · HIMSI UMKU                      │
└──────────────────────────────────────────────────────────────────┘
```

### Mobile (<640px)

```
┌────────────────┐
│ ▣ SIBINER   ☰  │
├────────────────┤
│ (hero stack)   │
│ tagline serif  │
│ [CTA gabung]   │
├────────────────┤
│ RAK BUKU       │
│ ┌────┐┌────┐ → │ ← horizontal snap,
│ │cvr ││cvr │   │   cover ratio 2:3
│ │★4.5││★5  │   │
│ └────┘└────┘   │
├────────────────┤
│ Diskusi rutin  │
│ (card ringkas) │
│ Pengurus list  │
│ Galeri 2 kolom │
│ Testimoni      │
│ CTA kontak     │
└────────────────┘
```

## 2. Component Tree

```
<RootLayout>                       (font serif display + sans body, warm tokens)
└── <HomePage>
    ├── <Navbar>                   minimal · [Masuk] [Gabung]
    ├── <HeroSection>              tagline serif · 2 CTA · ilustrasi buku
    ├── <RakBukuSection>           ⭐ centerpiece
    │   ├── <FilterChips>          kategori fiksi/nonfiksi/filosofi
    │   └── <BookCard[]>           cover · judul · rating · reviewer · quote hover
    ├── <DiskusiRutinSection>      jadwal + format
    ├── <PengurusSection>          fetch /publik/struktur?komunitas=SIBINER
    ├── <GaleriPreview>
    ├── <TestimoniCarousel>
    └── <CtaGabungFooter>          kontak IG/WA — tanpa form daftar (PRD §3)
```

## 3. State Matrix

| Section | Loading | Empty | Error | Success |
|---------|---------|-------|-------|---------|
| RakBuku | skeleton cover 2:3 ×4 | "Rak masih kosong — segera diisi!" + ilustrasi | disembunyikan | grid + filter aktif |
| FilterChips | — | disembunyikan jika <1 kategori | — | chip terpilih filled |
| Pengurus | skeleton avatar | section disembunyikan | fallback teks statis | list nama-jabatan |
| Galeri | blur placeholder | disembunyikan | disembunyikan | grid foto |
| Testimoni | — | disembunyikan | — | auto-rotate lembut |

## 4. Catatan UX Detail

- **Cover buku adalah hero visual** — kartu pakai shadow "buku berdiri" (spine gradient kiri); hover mengangkat buku + memunculkan kutipan favorit reviewer
- Rating bintang manual oleh reviewer (keputusan open question #1) — tampil `★ 4.5` mono kecil
- Filter chips client-side saja (data sudah ke-load sekali via ISR)
- Quote testimoni bergaya kutipan buku (tanda kutip serif besar, indentasi klasik) — konsisten tema literasi
- CTA gabung TIDAK membuka form (sesuai PRD: keanggotaan manual) — langsung tunjuk kontak IG/WA ketua; kurangi ekspektasi salah
- Ilustrasi hero: tumpukan buku flat-design warna palet (bukan stok foto generik)

> 💡 Learning: Identitas visual per-komunitas (blueprint vs warm library) itu mungkin karena komponen *struktural* (navbar, card shell, form controls) tetap dibagikan lewat `packages/ui`, sementara yang dibedakan cuma *design tokens* (warna, font display). Satu codebase, banyak kepribadian — prinsip yang sama dipakai design system besar seperti Polaris/Carbon.

## 5. Responsive Notes

| Breakpoint | Perilaku |
|-----------|----------|
| `<640px` | rak buku horizontal-snap; hero tagline turun ukuran serif; pengurus jadi list vertikal |
| `640–1023px` | rak 3 kartu; galeri 2 kolom |
| `≥1024px` | rak 4 kartu; layout max-w-6xl |

---
*Terintegrasi:* PRD `sibiner-app.md` §3 · backend M11 (`proyeks` sebagai review buku) · `struktur-organisasi.md`
