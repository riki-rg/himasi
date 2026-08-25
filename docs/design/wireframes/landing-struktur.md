# Wireframe — Struktur Organisasi `/struktur` Landing Page

> Status: Draft v1 · Visual: Engineering Blueprint · Render: ISR panjang
> Sumber data: `GET /publik/struktur` · Konten: `docs/design/struktur-organisasi.md`

---

## 1. Layout ASCII

```
┌──────────────────────────────────────────────────────────────┐
│ STRUKTUR ORGANISASI                                          │
│ Periode Kepengurusan 2026-2027                  [Pilih periode ▾]
├──────────────────────────────────────────────────────────────┤
│ Tab: [Semua] [BitSI] [Sibiner]                               │
│                                                              │
│ === BPH ==================================================== │
│              +-------------------+                           │
│              |   [FOTO BESAR]    |                           │
│              |   Ketua Umum      |   <- kartu utama tengah   │
│              |   Nama Lengkap    |                           │
│              +-------------------+                           │
│     +----------+  +-----------+  +-----------+               │
│     | Wakil    |  | Sekretaris|  | Bendahara |               │
│     | [foto]   |  | [foto]    |  | [foto]    |               │
│     | Nama     |  | Nama      |  | Nama      |               │
│     +----------+  +-----------+  +-----------+               │
│                                                              │
│ === DIVISI PENGEMBANGAN DIRI  [badge: mengelola BitSI] ===== │
│   [Ketua Divisi]        [Sekretaris Divisi]                  │
│   Anggota Divisi: (grid avatar kecil, collapse >12)          │
│                                                              │
│ === DIVISI ORGANISASI  [badge: mengelola Sibiner] ========== │
│   ... pola sama ...                                          │
│                                                              │
│ === MEDKOM ============+  === EKOWIR =========+              │
│   (dua divisi berdampingan di desktop)       |               │
│                                              │               │
│ Arsip periode sebelumnya ->                                  │
└──────────────────────────────────────────────────────────────┘
```

## 2. Component Tree

```
<StrukturPage>
├── <PeriodeSelector>             dropdown periode aktif + arsip
├── <KomunitasTabs>
└── <OrgChartSection[]>
    ├── <BphBlock>                layout hierarkis khusus
    ├── <DivisiBlock>
    │   ├── <DivisiHeader>        nama + badge komunitas dikelola
    │   ├── <PengurusCard[]>      foto - nama - jabatan - link IG/portofolio
    │   └── <AnggotaGrid>         avatar kecil, collapse >12 orang
    └── <ArsipLink>
```

## 3. State Matrix

| Kondisi | Perilaku |
|---------|----------|
| Loading | skeleton kartu pengurus |
| Divisi tanpa penugasan | blok tetap tampil + "Jabatan kosong -- akan diisi" |
| Foto belum ada | inisial nama dalam lingkaran cobalt |
| Periode arsip dipilih | badge "ARSIP" amber; read-only |
| Error API | fallback teks statis build-time + retry |

## 4. UX Notes

- Ketua Umum paling atas, visual dominan -- hierarki jelas bagi pengunjung awam
- Hover kartu pengurus: ikon Instagram & portofolio (data M7) -- org chart sekaligus etalase branding anggota
- Badge "mengelola BitSI/Sibiner" klik-able menuju app komunitas
- Dropdown periode memungkinkan lihat riwayat kepengurusan lama (ADR D3)
- Anchor link per divisi untuk share (`/struktur#medkom`)

## 5. Responsive

| Breakpoint | Perilaku |
|-----------|----------|
| `<640px` | semua stack vertikal; BPH kartu full-width; tab scroll horizontal |
| `640-1023px` | BPH 2 kolom; divisi lain 1 kolom |
| `>=1024px` | Medkom & Ekowir berdampingan; max-w-5xl |

---
*Terintegrasi:* PRD landing §3 · openapi `/publik/struktur` · struktur-organisasi.md
