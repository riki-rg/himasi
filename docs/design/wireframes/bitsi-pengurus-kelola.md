# Wireframe — Pengurus: Kelola Karya & Kelola Kelas `/app/pengurus/*` App BitSI

> Status: Draft v1 · Guard: role pengurus · Sumber: M11 (karya) + M12 (kelas-materi)
> Pola tabel-CRUD standar — satu dokumen untuk dua modul (Sibiner memakai pola sama dengan label "Rak Buku" / "Sesi")

---

## 1. Layout ASCII

### Kelola Karya (showcase proyek)

```
┌──────────────────────────────────────────────────────────────┐
│ ← Pengurus            KARYA ANGGOTA         [ + Tambah ]     │
├──────────────────────────────────────────────────────────────┤
│ 🔍 Cari…   Filter: [Semua] [Published] [Draft]               │
├──────────────────────────────────────────────────────────────┤
│ ┌────────┐ Sensor Suhu IoT          🟢 published             │
│ │thumb   │ oleh: Eka S. · teknologi: Arduino, ESP8266        │
│ │        │ [ Edit ]  [ Lihat publik ]  [ ⋮ Hapus/Unpub ]      │
│ ┌────────┐ Landing Page UMKU         🟡 draft                 │
│ │ …      │ oleh: Rizky M.                                     │
│ └────────┘                                                    │
│                        pagination                             │
└──────────────────────────────────────────────────────────────┘

Form tambah/edit (dialog atau halaman):
┌──────────────────────────────────────────┐
│ JUDUL PROYEK*  […………………]                 │
│ DESKRIPSI     [textarea]                 │
│ THUMBNAIL*    [pilih file ≤5MB] [preview]│
│ LINK DEMO     [https://…]                │
│ LINK REPO     [https://github.com/…]     │
│ TEKNOLOGI     [Arduino ×] [ESP8266 ×] [+]│
│ PEMBUAT*      [cari anggota……… ▾]        │
│ STATUS        (•) draft  ( ) published   │
│           [Batal]  [Simpan]              │
└──────────────────────────────────────────┘
```

### Kelola Kelas & Materi

```
┌──────────────────────────────────────────────────────────────┐
│ KELAS BELAJAR                           [ + Kelas Baru ]     │
├──────────────────────────────────────────────────────────────┤
│ Web Dev Dasar    Sab 16.00   pengajar: Bima   📚8  [Kelola →]│
│ IoT Dasar        Min 09.00   pengajar: Dimas 📚5  [Kelola →] │
│                                                              │
│ Halaman kelola kelas → daftar materi:                        │
│ [ + Tambah Materi ]                                          │
│ 01 Pengenalan HTML      file.pdf   2MB   [⬆][⬇][edit][hapus] │
│ 02 CSS Dasar            link       —     [⬆][⬇][edit][hapus] │
│    drag-handle urutan · edit inline judul                    │
└──────────────────────────────────────────────────────────────┘
```

## 2. Component Tree

```
<KelolaKaryaPage>
├── <Toolbar>                     search + filter status + tambah
├── <KaryaRow[]>                  thumb - judul - pembuat - status - aksi
└── <KaryaFormDialog>             semua field M11 + validasi URL
<KelolaKelasPage>
├── <KelasTable>
└── <KelasDetailAdmin>
    ├── <MateriSortableList>      drag-drop urutan (keyboard accessible)
    └── <MateriFormDialog>        tipe file/link · upload ≤ batas ADR D5
```

## 3. State Matrix

| Kondisi | Perilaku |
|---------|----------|
| Kosong | ilustrasi + CTA "Tambah pertama" |
| Simpan sukses | toast + row update optimis |
| Hapus | konfirmasi dialog sebut nama item; soft-delete bila ada relasi |
| Upload gagal | retry per-file; form tidak reset |
| Pembuat bukan member aktif | peringatan inline di form |

## 4. UX Notes

- Status draft/published = kontrol publish tanpa hapus; published langsung terlihat di halaman publik showcase
- Drag-drop urutan materi punya tombol panah alternatif (aksesibilitas keyboard — checklist skill ui-ux-pro-max)
- Teknologi sebagai chip multi-input, tersimpan array JSON
- Semua aksi CRUD pengurus scoped komunitas BITSI di backend — UI tidak menampilkan pilihan komunitas lain

## 5. Responsive

Mobile: baris kartu jadi stack vertikal dengan menu aksi `⋮`; form dialog jadi full-screen sheet.
