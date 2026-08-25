# Wireframe — Detail Rapat Member `/app/rapat/:id`

> Route: `/app/rapat/:id` · Guard: member BitSI disetujui · Status: Draft v1
> Fungsi: satu halaman yang membawa member melalui seluruh siklus rapat — **sebelum (info) → saat (absen) → sesudah (notulen)** — dengan momen paling krusial: tombol scan absensi
> Visual: Engineering Blueprint (paper × cobalt)

---

## 1. Layout ASCII

### Kondisi A — Rapat mendatang (`dijadwalkan`)

```
┌──────────────────────────────────────────┐
│ ← Rapat                                  │
├──────────────────────────────────────────┤
│ ◉ pulse-dot RAPAT MINGGUAN      [H-2]    │
│ Sabtu, 29 Agu 2026 · 16.00–17.30 WIB     │
│ 📍 Lab SI                                 │
│                                          │
│ ┌ AGENDA ──────────────────────────────┐ │
│ │ 1. Evaluasi program kerja            │ │
│ │ 2. Persiapan workshop                │ │
│ │ 3. Laporan kas                       │ │
│ └──────────────────────────────────────┘ │
│                                          │
│ ⏰ Jangan lupa absen scan nanti!         │
│ Absensi dibuka saat rapat berlangsung.   │
│                                          │
│ ┌ PESERTA (12) ────────────────────────┐ │
│ │ 👤 Alya P. 👤 Bima P. 👤 Citra D. …  │ │
│ └──────────────────────────────────────┘ │
│                                          │
│ [ Tambah ke Kalender ]  (ics download)   │
└──────────────────────────────────────────┘
```

### Kondisi B — Jendela absensi AKTIF (rapat berlangsung)

```
┌──────────────────────────────────────────┐
│ ← Rapat              🔴 BERLANGSUNG      │
├──────────────────────────────────────────┤
│ RAPAT MINGGUAN                           │
│ Sabtu 29 Agu · 16.00–17.30 · Lab SI      │
│                                          │
│ ╔══════════════════════════════════════╗ ║
│ ║  Belum absen? Sekarang waktunya!     ║ ║
│ ║                                      ║ ║
│ ║   ┌──────────────────────────────┐   ║ ║
│ ║   │   📷  SCAN ABSENSI           │   ║ ║
│ ║   └──────────────────────────────┘   ║ ║
│ ║        tombol cobalt besar           │ ║
│ ║   atau ketik kode manual             │ ║
║ ╚══════════════════════════════════════╝ ║
│                                          │
│ Agenda & peserta (collapse di bawah)     │
└──────────────────────────────────────────┘

       ↓ tekan SCAN → overlay kamera fullscreen ↓
┌──────────────────────────────────────────┐
│         ┌ ─ ─ ─ ─ ─ ─ ─ ┐               │
│        │   ▛▀▀▀▜ frame   │              │ ← viewfinder,
│        │   ▌scan▐ cobalt │              │   corner brackets
│         └ ─ ─ ─ ─ ─ ─ ─ ┘               │
│                                              │
│   Arahkan ke QR yang ditampilkan pengurus    │
│                                              │
│ [ Ketik kode manual ]          [ ✕ Tutup ]   │
└──────────────────────────────────────────┘
```

### Kondisi C — Setelah absen / rapat selesai

```
┌──────────────────────────────────────────┐
│ ← Rapat            ✅ SELESAI            │
├──────────────────────────────────────────┤
│ RAPAT MINGGUAN                           │
│ Sabtu 29 Agu · 16.00–17.30 · Lab SI      │
│                                          │
│ ┌ KEHADIRANMU ─────────────────────────┐ │
│ │ ✅ HADIR · tercatat 16.07            │ │ ← hijau sukses;
│ └──────────────────────────────────────┘ │   izin = amber +
│                                          │   catatanmu
│ ┌ NOTULEN ─────────────────────────────┐ │
│ │ 1. Workshop ditunda ke minggu depan… │ │
│ │ 2. Kas bulanan Agustus cukup…        │ │
│ │ 📎 lampiran-notulen.pdf  (unduh)     │ │
│ └──────────────────────────────────────┘ │
│                                          │
│ Agenda · Peserta & kehadiran (collapse)  │
└──────────────────────────────────────────┘
```

## 2. Component Tree

```
<RapatDetailPage route="/app/rapat/:id">
├── <StatusBanner>                  H-x merah muda / BERLANGSUNG pulse / ✅ selesai / dibatalkan abu
├── <RapatMetaCard>                 tanggal · jam · tempat/link online
├── <AbsensiSection>                ⭐ konten berganti per fase:
│   ├── <BelumDibukaNote>           kondisi A
│   ├── <ScanCta>                   kondisi B → buka <ScannerOverlay>
│   │   ├── <CameraViewfinder>      html5-qrcode, frame sudut cobalt
│   │   ├── <ManualTokenInput>      fallback tanpa kamera
│   │   └── <AbsenResultToast>      sukses vibrate + check animasi
│   └── <KehadiranPribadi>          kondisi C: status + waktu absenku
├── <AgendaList>
├── <NotulenCard>                   hanya setelah selesai; + <LampiranLink[]>
└── <PesertaCollapse>               daftar + status kehadiran (setelah selesai)
```

## 3. State Matrix

| Kondisi | Perilaku |
|---------|----------|
| Loading | Skeleton meta card + agenda lines |
| `dijadwalkan` | Info lengkap + note "absensi belum dibuka" + tombol kalender |
| Jendela aktif | ScanCta besar; polling status tiap ~30s agar berhenti sendiri saat rapat selesai |
| Scanner dibuka | Minta izin kamera sekali; tolak izin → otomatis tawarkan input manual |
| Scan sukses | Vibrate 100ms ✓ + layar konfirmasi "Hadir tercatat 16.07" lalu banner hijau menggantikan ScanCta |
| Token kedaluwarsa (410) | Toast "QR sudah ganti — coba scan lagi", scanner tetap terbuka |
| Sudah absen (409) | Toast ramah "Kamu sudah absen tadi 😄" + tutup scanner |
| Token salah (422) | Inline "kode tidak dikenal" pada input manual |
| Rapat `dibatalkan` | Banner abu-abu atas + konten tetap bisa dibaca |
| Notulen kosong | "Notulen menyusul setelah rapat" |

## 4. Catatan UX Detail

- **Satu halaman, tiga wajah** — komposisi konten berubah sesuai fase rapat; user tidak perlu paham state machine, cukup lihat banner status
- Scanner: frame sudut cobalt (bukan garis penuh) — area scan lebih besar & jelas arahnya
- Fallback **ketik kode manual** selalu tersedia: kamera rusak/hp lawas tidak boleh jadi alasan gagal presensi; kode = bagian terakhir payload QR
- Hasil absen menampilkan waktu persis ("16.07") — rasa "tercatat sungguhan", bukan cuma toast hilang
- Lampiran notulen pakai unduh langsung; preview inline skip (hemat effort, PDF viewer browser sudah cukup)
- Tombol kalender generate file `.ics` client-side dari data rapat

> 💡 Learning: Perhatikan bahwa error API kita (410 QrExpired · 409 SudahAbsen · 422 invalid) dipetakan satu-per-satu ke reaksi UI yang berbeda — inilah kenapa RFC 7807 dengan `type` URI itu berguna: frontend bisa `switch(type)` tanpa parse string pesan.

## 5. Responsive Notes

| Breakpoint | Perilaku |
|-----------|----------|
| `<640px` | Semua stack vertikal; ScanCta full-width sticky bottom saat jendela aktif (selalu terlihat!) |
| `≥768px` | Meta card + agenda grid 2 kolom; ScanCta inline dalam kartu |
| Scanner overlay | Fullscreen di semua ukuran; tombol tutup pojok kanan atas thumb-reach |

---
*Terintegrasi:* PRD `bitsi-app.md` §3 · openapi.yaml `POST /rapat/{id}/absen` (+error 410/409) · wireframe `bitsi-presenter-qr.md` (pasangan sisi pengurus)
