# Wireframe — Katalog Kelas `/app/kelas` & Detail `/app/kelas/:id` App BitSI

> Status: Draft v1 · Visual: Engineering Blueprint · Guard: member disetujui
> Sumber: backend M12 (modul Kelas & Materi) · US-24

---

## 1. Layout ASCII

### Katalog — daftar kelas per bidang

```
┌──────────────────────────────────────────────────┐
│ ← Dashboard          KELAS BELAJAR               │
├──────────────────────────────────────────────────┤
│ Filter bidang: [Semua] [Web] [IoT] [Jaringan] [Server]
│                                                  │
│ ┌──────────────┐ ┌──────────────┐ ┌───────────┐ │
│ │ ◉ WEB DEV    │ │ ◉ IOT DASAR  │ │ ◉ JARINGAN│ │
│ │   DASAR      │ │              │ │  DASAR    │ │
│ │ Sabtu 16.00  │ │ Minggu 09.00 │ │ Rab 19.00 │ │
│ │ Lab SI       │ │ Lab IoT      │ │ Daring    │ │
│ │ 👨‍🏫 Kak Bima │ │ 👨‍🏫 Kak Dimas│ │ 👨‍🏫 Kak Eka│ │
│ │ ──────────   │ │              │ │           │ │
│ │ 📚 8 materi  │ │ 📚 5 materi  │ │ 📚 3 mtrl │ │
│ └──────────────┘ └──────────────┘ └───────────┘ │
│        tap kartu → detail kelas                  │
└──────────────────────────────────────────────────┘
```

### Detail kelas + materi

```
┌──────────────────────────────────────────────────┐
│ ← Kelas                                          │
├──────────────────────────────────────────────────┤
│ ◉ WEB DEV DASAR                                  │
│ Bidang: Web · Jadwal rutin: Sabtu 16.00 · Lab SI │
│ Pengajar: Bima Pratama (Ketua Divisi)            │
│                                                  │
│ Deskripsi singkat kelas…                         │
│                                                  │
│ MATERI SESI                                      │
│ ┌──────────────────────────────────────────────┐ │
│ │ 01 · Pengenalan HTML                [⬇ unduh]│ │
│ │ 02 · CSS Dasar                      [⬇]      │ │
│ │ 03 · Layout Flexbox                 [⬇]      │ │
│ │ 04 · Intro JavaScript               [🔗 link]│ │
│ └──────────────────────────────────────────────┘ │
│    urutan sesi · tipe file/link · unduh langsung │
└──────────────────────────────────────────────────┘
```

## 2. Component Tree

```
<KelasCatalogPage>
├── <BidangFilterChips>
└── <KelasCard[]>               nama - jadwal - pengajar - count materi
<KelasDetailPage route="/app/kelas/:id">
├── <KelasHeader>               meta + pengajar (link ke profil/struktur)
├── <MateriList>
│   └── <MateriRow[]>           nomor urut · judul · ikon tipe · aksi unduh/buka
```

## 3. State Matrix

| Kondisi | Perilaku |
|---------|----------|
| Loading | skeleton kartu ×3 |
| Belum ada kelas | "Belum ada kelas dibuka — nantikan info" |
| Materi kosong | "Materi akan diunggah setelah sesi pertama" |
| Materi file besar | indikator progress unduhan |
| Akses non-member (403) | guard halaman menangkap sebelum render |

## 4. UX Notes

- Publik hanya melihat daftar nama kelas via API; **materi hanya untuk member** (403) — sesuai US-24 AC
- Unduhan pakai signed URL / streaming Laravel Storage
- Nomor urut materi = urutan sesi belajar; jangan sort alfabetis
- Pengajar bisa berupa beberapa orang → avatar stack

## 5. Responsive

Mobile: kartu stack vertikal; baris materi full-width, target sentuh 44px+. Desktop: grid 3 kolom.
