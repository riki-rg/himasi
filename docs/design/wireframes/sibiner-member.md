# Wireframe — Member Area Sibiner: Diskusi & Bacaan

> Status: Draft v1 · Guard: member Sibiner · Visual: Warm Library (krem × hijau hutan × serif Editorial Classic: Cormorant Garamond + Libre Baskerville)
> Prinsip: **mirror** pola BitSI (`bitsi-rapat-detail.md`, `bitsi-kelas.md`) dengan label literasi — dokumen ini hanya mencatat perbedaan; sisanya ikut pola asal

---

## 1. Pemetaan Pola BitSI → Sibiner

| Halaman BitSI | Padanan Sibiner | Perbedaan konten |
|---------------|-----------------|------------------|
| `/app/rapat` list+detail | `/app/diskusi` | istilah "Diskusi" bukan "Rapat"; agenda = poin bahasan bab |
| `/app/kelas` katalog+detail | `/app/bacaan` | "Kelas"→"Bacaan/Sesi"; materi = rangkuman bab, catatan diskusi |
| Scan absensi QR | sama persis | komponen share `packages/ui` |
| `/app/pengurus/kelola` | rak-buku & sesi pengurus | label beda, form identik |

## 2. Layout ASCII — `/app/bacaan` detail (yang paling berbeda)

```
┌──────────────────────────────────────────────┐
│ ← Bacaan                                     │
├──────────────────────────────────────────────┤
│ ┌──────┐  LASKAR PELANGI                     │
│ │cover │  Andrea Hirata · 428 hlm           │
│ │2:3   │  ★★★★☆ 4.5 — review oleh Citra D.  │
│ └──────┘                                     │
│                                              │
│ SESI DISKUSI                                 │
│ ┌──────────────────────────────────────────┐ │
│ │ Sesi 1-3 · Sabtu ini 19.30               │ │
│ │ 📎 rangkuman-bab1-3.pdf        [⬇ unduh] │ │
│ │ 🔗 peta karakter (Notion)      [buka]    │ │
│ ├──────────────────────────────────────────┤ │
│ │ Sesi 4-6 · minggu depan     ⏳ menyusul  │ │
│ └──────────────────────────────────────────┘ │
│                                              │
│ KUTIPAN FAVORIT                              │
│ ❝ Hidup harus terus berjuang… ❞             │
│    — catatan Citra, sesi review              │
└──────────────────────────────────────────────┘
```

## 3. State Matrix

Identik tabel state matrix di `bitsi-rapat-detail.md` dan `bitsi-kelas.md` — termasuk error mapping 410/409/422 untuk scan absensi. Tambahan:

| Kondisi | Perilaku |
|---------|----------|
| Cover buku kosong | placeholder ilustrasi buku generik warna palet (open question #2 PRD) |
| Rating belum ada | sembunyikan baris rating |

## 4. UX Notes

- Cover 2:3 konsisten semua kartu buku (rak publik + area member)
- Kutipan favorit ditampilkan bergaya sitat klasik (serif italic, tanda kutip besar) — pembeda rasa literasi
- Rangkuman hanya bisa diunduh member (403 untuk publik) — sesuai US-24

## 5. Responsive

Mengikuti pola asal; aksen visual diganti token Warm Library.

---
*Terintegrasi:* PRD `sibiner-app.md` §3 · wireframes `bitsi-rapat-detail.md`, `bitsi-kelas.md`
