# Wireframe — Mode Presenter QR `/app/pengurus/presenter`

> Route: `/app/pengurus/presenter` · Guard: role pengurus BITSI · Status: Draft v1
> Fungsi: layar yang ditampilkan pengurus di proyektor/laptop saat mulai rapat — peserta scan QR untuk presensi (ADR D1: payload berotasi tiap 60 detik anti titip-absen)
> Ini fitur **paling sering dipakai di ruangan** — desainnya harus tenang di mata & tahan gangguan

---

## 1. Layout ASCII

### Layar A — Pemilihan Rapat (sebelum presentasi)

```
┌──────────────────────────────────────────────────┐
│ ← Kembali          Mode Absensi                  │
├──────────────────────────────────────────────────┤
│                                                  │
│  Pilih rapat hari ini:                           │
│                                                  │
│  ┌────────────────────────────────────────────┐  │
│  │ ◉ RAPAT MINGGUAN                           │  │
│  │ Sabtu 29 Agu · 16.00 · Lab SI              │  │
│  │ ⏱ dimulai 12 menit lagi                    │  │
│  │                          [ MULAI ABSENSI → ]│  │
│  └────────────────────────────────────────────┘  │
│                                                  │
│  ┌────────────────────────────────────────────┐  │
│  │ ○ EVALUASI WORKSHOP                        │  │
│  │ Sabtu 29 Agu · 19.00 · Online              │  │
│  └────────────────────────────────────────────┘  │
│                                                  │
│  Tidak ada rapat cocok? [Buat rapat baru]        │
│                                                  │
└──────────────────────────────────────────────────┘
```

### Layar B — Mode Presentasi (fullscreen, landscape proyektor)

```
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║   RAPAT MINGGUAN · Sabtu 29 Agu            [ ✕ Keluar ]     ║
║   Lab SI                                                     ║
║                                                              ║
║              ┌─────────────────────────┐                     ║
║              │ ▛▀▀▀▀▀ ▀▜  ▜▀▀▀▀▀▜      │  ← QR besar         ║
║              │ ▌ ◼◼◻◼ ◻ ◼ ◻◼◻◼ ◼▐      │     min. 320px,     ║
║              │ ▌ ◼◻ ◼◼◼ ◼◻ ◼ ◼◼▐       │     kontras maks    ║
║              │ ▌ ◻◼◻ ◻◻◼ ◼◼ ◻ ◼ ▐      │                     │
║              │ ▚▄▄▄▄ ▄▟  ▙▄▄▄▄▟        │                     ║
║              └─────────────────────────┘                     ║
║                                                              ║
║         ⟳ rotasi otomatis · ▓▓▓▓▓▓▓░░░ 23s                   ║
║           (progress ring menipis sampai refresh)             ║
║                                                              ║
║   👥 14 sudah absen                                          ║
║   ✨ baru masuk: Alya Putri · Bima Pratama                   ║
║                                                              ║
║   Scan pakai kamera HP kamu — tidak perlu install apapun     ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
```

### Mobile portrait (proyektor gak ada? HP + kabel TV juga bisa)

```
┌──────────────┐
│ ✕        ⏸  │ ← pause (kalau pengurus perlu bicara dulu)
│              │
│ RAPAT        │
│ MINGGUAN     │
│              │
│ ┌──────────┐ │
│ │ ▛▀▀▀▜    │ │
│ │ ▌ QR  ▐  │ │ ← QR tetap dominan,
│ │ ▙▄▄▄▟    │ │   lebar penuh aman
│ └──────────┘ │
│ ⟳ 41s ▓▓▓░  │
│              │
│ 👥 14 absen  │
└──────────────┘
```

## 2. Component Tree

```
<AppLayout guard="pengurus">
└── <PresenterPage>
    ├── <RapatPickerScreen>             layar A — daftar rapat hari ini
    │   └── <RapatPilihCard>            status + countdown mulai
    └── <PresentationMode>              layar B — request fullscreen + wakelock
        ├── <PresenterHeader>           judul rapat · lokasi · keluar
        ├── <QrCanvas>                  render payload → QR image
        │   └── <RotationRing>          progress countdown 60s
        ├── <AbsenCounter>              jumlah + nama terbaru muncul
        └── <PauseOverlay>              mode jeda saat pengurus bicara
```

## 3. State Matrix

| Kondisi | Perilaku |
|---------|----------|
| Tidak ada rapat hari ini | Layar kosong ramah + tombol buat rapat cepat (judul+tempat saja) |
| Rotasi payload | Fetch `GET /rapat/{id}/qr` di **t=52s** (bukan 60) → QR baru crossfade halus, tanpa gap kosong |
| Fetch rotasi gagal | QR lama tetap tampil + badge kecil "menyegarkan…" + retry backoff 5s; JANGAN blank |
| Counter absen | Poll `GET /rapat/{id}/rekap` tiap 10s; nama baru slide-in dari bawah dengan highlight cobalt |
| Tab kehilangan fokus / sleep | **Wake Lock API** aktifkan saat masuk mode; kalau browser tidak support → warning sekali "jaga layar tetap menyala" |
| User tekan Esc / ✕ | Konfirmasi inline kecil ("Akhiri sesi absensi?") — cegah keluar tak sengaja |
| Sesi diakhiri | Ringkasan singkat: total hadir · izin · belum + tombol lihat rekap lengkap |

## 4. Catatan UX Detail

- **QR selalu kontras hitam-putih murni** — jangan pernah warnai QR dengan cobalt/sky; scanner butuh kontras tinggi. Identitas blueprint cukup di area sekeliling QR
- Ukuran QR adaptif viewport (`min(70vh, 70vw)`), quiet-zone minimal 4 modul biar semua kamera kebaca
- Progress ring rotasi itu penting: member yang telat scan melihat "masih sempat" atau "tunggu sebentar, mau ganti"
- Nama yang baru absen muncul sebagai **social proof** — ruangan langsung tahu sistemnya jalan; tapi hanya nama depan + inisial (privasi: `Alya P.`)
- Mode pause (⏸): freeze counter & sembunyikan QR saat pengurus ingin full atensi — hindari orang absen sambil lalu sebelum pengumuman penting dibacakan
- Semua polling berhenti otomatis saat tab hidden (hemat battery proyektor/HP)

> 💡 Learning: Pola "fetch lebih awal dari kedaluwarsa" (t=52s dari TTL 60s) adalah teknik umum token lifecycle — sama seperti refresh token OAuth. Kalau tunggu pas 60 detik, ada celah milidetik-di-detik di mana layaran kosong dan antrean orang panik.

## 5. Responsive Notes

| Konteks | Perilaku |
|---------|----------|
| Proyektor/laptop landscape | Layout center seperti Layar B; header tipis atas |
| HP portrait | QR max-width, header stack vertikal, tombol ✕/⏸ di pojok atas thumb-reach |
| Split-screen / window kecil | QR turun skala proporsional, counter pindah bawah QR |

---
*Terintegrasi:* PRD `bitsi-app.md` §3 · ADR D1 (rotasi HMAC 60s) · openapi.yaml `GET /rapat/{id}/qr` · wireframe `bitsi-dashboard.md` (entry point dari hero card)
