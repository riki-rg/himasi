# Wireframe — Dashboard `/app` App BitSI

> Route: `/app` · Render: Client SPA behind auth guard · Status: Draft v1
> Referensi visual: Engineering Blueprint — paper × cobalt × sky · Plex Sans/Mono · blueprint grid halus di header saja (hemat visual noise untuk area kerja)
> Peran halaman: **hub** semua aktivitas member; menentukan pola navigasi seluruh app

---

## 1. Layout ASCII

### Desktop (≥1024px) — sidebar kiri

```
┌────────────────────────────────────────────────────────────────────┐
│ ┌ SIDEBAR (240px) ─┐  ┌ KONTEN ──────────────────────────────────┐ │
│ │ ▣ BitSI          │  │  Selamat siang, Rizz 👋                  │ │
│ │                  │  │  anggota BITSI · angkatan 2021           │ │
│ │ ●  Dashboard     │  │  ────────────────────────────────────    │ │
│ │ ○  Rapat         │  │                                          │ │
│ │ ○  Kelas         │  │  ┌ RAPAT TERDEKAT (hero card) ─────────┐ │ │
│ │ ○  Profil        │  │  │ ◉ pulse-dot  RAPAT MINGGUAN         │ │ │
│ │                  │  │  │ Sabtu, 29 Agu · 16.00               │ │ │
│ │ ──────────       │  │ │ Lab SI · agenda: persiapan workshop │ │ │
│ │ PENGURUS         │  │  │ [ Lihat Detail ]  ⏱ H-2             │ │ │
│ │ (kondisional)    │  │  └─────────────────────────────────────┘ │ │
│ │ ┌──────────────┐ │  │                                          │ │
│ │ │🛠 Approve (3)│ │  │  KELAS TERDEKAT                          │ │
│ │ │🛠 Presenter  │ │  │  ┌──────────┐┌──────────┐┌──────────┐   │ │
│ │ │🛠 Kelola     │ │  │  │Web Dev   ││IoT Dasar ││Jaringan  │   │ │
│ │ └──────────────┘ │  │  │Dasar     ││          ││          │   │ │
│ │                  │  │  │Sab 16.00 ││Min 09.00 ││Rab 19.00 │   │ │
│ │ ──────────       │  │  │Kak Bima  ││Kak Dimas ││Kak Eka   │   │ │
│ │ 👤 Rizz          │  │  └──────────┘└──────────┘└──────────┘   │ │
│ │ [Keluar]         │  │              [Semua kelas →]             │ │
│ └──────────────────┘  │                                          │ │
│                       │  PENGUMUMAN                              │ │
│                       │  📢 Penting: bawa laptop saat workshop   │ │
│                       │  📢 Rutinan pindah ke Lab SI bulan depan │ │
│                       │              [Semua →]                   │ │
│                       │                                          │ │
│                       │  KEHADIRANKU (mini stat)                 │ │
│                       │  ▓▓▓▓▓▓▓░░░ 7/9 rapat (78%)             │ │
│                       └──────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────┘
```

### Mobile (<640px) — bottom tab bar

```
┌────────────────────┐
│ ▣ BitSI       👤   │
├────────────────────┤
│ Selamat siang,     │
│ Rizz 👋            │
│ ────────────────── │
│ ┌────────────────┐ │
│ │◉ RAPAT         │ │ ← hero card full-width,
│ │MINGGUAN        │ │   cobalt border kiri tebal
│ │Sab 29 · 16.00  │ │
│ │⏱ H-2          │ │
│ │[Detail] [📍Absen]│ ← tombol Absen hanya muncul
│ └────────────────┘ │   saat window rapat aktif!
│                    │
│ KELAS TERDEKAT     │
│ ┌────┐┌────┐ →scroll│ ← horizontal snap scroll
│ │Web ││IoT │      │
│ └────┘└────┘      │
│                    │
│ PENGUMUMAN         │
│ • Bawa laptop...   │
│ • Pindah Lab SI... │
│                    │
│ Kehadiran: 78%     │
├────────────────────┤
│ 🏠     📅    📚  👤 │ ← bottom tab: Home·Rapat·
│Home  Rapat Kelas Me │   Kelas·Profil (thumb-reach)
└────────────────────┘
```

## 2. Component Tree

```
<AppLayout>                          (guard: butuh login + status disetujui)
├── <Sidebar> / <BottomTabBar>       responsif — swap by breakpoint
│   └── <PengurusMenu>               kondisional role pengurus BITSI
├── <DashboardPage>
│   ├── <GreetingHeader>             nama · badge komunitas · angkatan
│   ├── <RapatTerdekatCard>          ⭐ hero — prioritas visual tertinggi
│   │   ├── <CountdownBadge>         H-x / "BERLANGSUNG" / "hari ini 16.00"
│   │   └── <AbsensiCta>             muncul kondisional saat window QR aktif
│   ├── <KelasTerdekatGrid>          Card[] horizontal-scroll mobile
│   │   └── <KelasCard>              nama · jadwal rutin · pengajar
│   ├── <PengumumanList>             item prioritas=penting dgn badge amber
│   └── <StatKehadiran>              progress bar personal
```

## 3. State Matrix

| Section | Loading | Empty | Error | Success |
|---------|---------|-------|-------|---------|
| Greeting | skeleton bar | — | — | nama dari profil |
| RapatTerdekat | skeleton card | "Belum ada rapat dijadwalkan — pantau terus ya" | inline retry kecil | card lengkap + countdown |
| AbsensiCta | — | disembunyikan | disembunyikan | tombol cobalt besar hanya saat `window_aktif` |
| KelasTerdekat | skeleton ×3 | section disembunyikan | disembunyikan | grid/scroll kartu |
| Pengumuman | skeleton row ×2 | disembunyikan | disembunyikan | list max 3 |
| StatKehadiran | shimmer bar | "Belum ada riwayat" | disembunyikan | bar + persen |
| PengurusMenu | — | disembunyikan (bukan pengurus) | — | menu + badge count pendaftar pending |

**Guard states (sebelum sampai dashboard):**

| Kondisi | Halaman yang tampil |
|---------|---------------------|
| Belum login | redirect `/login` + toast "sesi berakhir" |
| Login tapi status `pending` | layar khusus ⏳ "Menunggu persetujuan ketua" + kontak pengurus |
| Login tapi ditolak | layar "Pendaftaran belum disetujui" + alasan (jika ada) |
| Role pengurus | sidebar memunculkan blok PENGURUS |

## 4. Catatan UX Detail

- **Hero card = rapat terdekat**, bukan widget seremonial — ini alasan utama member buka app (cek jadwal & absen). Tombol `📍 Absen` hanya muncul saat jendela absensi aktif (dari `GET /rapat/{id}` flag), ukuran besar biar gampang ditekan saat antre masuk kelas
- Countdown hidup (`H-2` → `Hari ini` → `Berlangsung`) pakai interval lokal, tanpa polling API
- Badge count merah di menu `Approve` = jumlah pendaftar pending — dorong pengurus cepat bertindak
- Bottom tab mobile 5 item max; `Kelas` bisa digabung ke `Home` kalau terasa penuh
- Semua link kartu navigasi ke detail route masing-masing (`/app/rapat/:id`, `/app/kelas/:id`)
- Dark mode mengikuti preferensi sistem (blueprint theme punya varian gelap: ink background)

## 5. Responsive Notes

| Breakpoint | Navigasi | Konten |
|-----------|----------|--------|
| `<640px` | bottom tab bar | hero card full-width; kelas horizontal-snap; stat ringkas 1 baris |
| `640–1023px` | sidebar collapse jadi ikon rail | konten max-w-3xl center |
| `≥1024px` | sidebar penuh 240px fixed | grid konten lebar, kelas 3 kolom |

---
*Terintegrasi:* PRD `bitsi-app.md` §3 & §5 · wireframe `bitsi-daftar.md`
