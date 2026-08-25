# Wireframe — Approve Pendaftar `/app/pengurus/pendaftar` App BitSI

> Status: Draft v1 · Guard: role pengurus BITSI · Sumber: US-06 + ADR D6

---

## 1. Layout ASCII

```
┌──────────────────────────────────────────────────────────────┐
│ ← Pengurus          PERSETUJUAN ANGGOTA BARU    badge [3]     │
├──────────────────────────────────────────────────────────────┤
│ Tab: [⏳ Menunggu (3)]  [✅ Disetujui]  [🚫 Ditolak]          │
├──────────────────────────────────────────────────────────────┤
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ ┌────┐  Rizky Maulana                    NIM 2210500xyz  │ │
│ │ │foto│  Angkatan 2022 · Sistem Informasi                  │ │
│ │ │/ini│  ✉ rizky@…  📱 0812…                              │ │
│ │ └────┘  🔗 portofolio.dev/rizky   📷 @rizky.dev           │ │
│ │                                                          │ │
│ │ Daftar: 2 jam lalu                                       │ │
│ │ [ ✓ Setujui ]              [ ✕ Tolak ]                   │ │
│ │    cobalt solid             outline merah                │ │
│ └──────────────────────────────────────────────────────────┘ │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ … pendaftar berikutnya …                                 │ │
│ └──────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────┘

Kondisi tolak → dialog alasan:
┌──────────────────────────────────────┐
│ Alasan penolakan (opsional):         │
│ ┌──────────────────────────────────┐ │
│ │ Bukan anggota HIMSI aktif…       │ │
│ └──────────────────────────────────┘ │
│ ⚠ Alasan akan tampil ke pendaftar    │
│ [ Batal ]              [ Tolak → ]   │
└──────────────────────────────────────┘
```

## 2. Component Tree

```
<PendaftarPage>
├── <StatusTabs>                  pending / disetujui / ditolak (+count)
├── <PendaftarCard[]>
│   ├── <IdentitasRingkas>        foto - nama - NIM - kontak - links
│   ├── <WaktuDaftar>             relative time
│   ├── <ApproveButton>           optimistic update + undo toast 5 detik
│   └── <RejectFlow>              dialog alasan → konfirmasi
└── <EmptyState>
```

## 3. State Matrix

| Kondisi | Perilaku |
|---------|----------|
| Loading | skeleton kartu ×2 |
| Tidak ada pending | "Tidak ada pendaftar menunggu 🎉" + ilustrasi tenang |
| Approve sukses | kartu slide-out + undo toast ("Dibatalkan?") 5 detik |
| Reject tanpa alasan | boleh (opsional); konfirmasi tetap |
| Race (sudah diproses admin lain, 409) | toast "Sudah diproses" + refresh list |
| Badge count sidebar | update realtime setelah aksi |

## 4. UX Notes

- **Undo toast setelah approve** — kesalahan klik tidak fatal; lebih aman daripada dialog konfirmasi ganda yang menyebalkan
- Alasan tolak opsional tapi didorong: pendaftar melihatnya di layar RejectedScreen (bitsi-auth.md)
- Kartu menampilkan semua data yang diisi saat daftar — keputusan cukup dari satu layar, tanpa buka-buka tab
- Semua aksi tercatat (approved_by + disetujui_pada) untuk audit periode

## 5. Responsive

Mobile: kartu full-width, tombol aksi sticky bottom per kartu aktif. Desktop: max-w-3xl list.
