# PRD — Aplikasi BitSI (React SPA)

> Status: Draft v1 · Tanggal: 2026-08-25 · Pemilik: rizz
> Konsumen endpoint Backend API HIMSI UMKU (`docs/api/openapi.yaml`)
> Komunitas: **BitSI — Bit Of Sistem Informasi** · Web Dev · IoT · Jaringan · Server
> Domain rencana: `bitsi.himsiumku.[domain]` · dev: `*.vercel.app`/`*.netlify.app`

---

## 1. Latar Belakang

BitSI butuh rumah digital sendiri yang memperkenalkan komunitas sekaligus melayani anggotanya: registrasi member, jadwal kelas & rapat, absensi QR, materi belajar, showcase karya, dan tagihan kas. Semua data dari backend HIMSI UMKU — aplikasi ini murni presentation layer.

## 2. Goals / Non-goals

### Goals
1. Memperkenalkan BitSI secara menarik ke calon anggota (public area) → konversi daftar
2. Layanan mandiri member: profil, jadwal, absensi, materi, iuran pribadi
3. Alat kerja pengurus BitSI: approve pendaftar, mode presenter QR absensi, kelola karya & kelas
4. Cepat & ringan (SPA + code splitting), mobile-first (dipakai saat rapat/kelas via HP)

### Non-goals
- Chat/forum realtime
- Fitur lintas-komunitas (itu wilayah landing page / backend)
- Panel admin pusat (kelola anggota massal, surat, keuangan global → tetap sisi admin Laravel)

## 3. Halaman & Route

### 🌐 Public *(tanpa login — memperkenalkan BitSI)*

| Route | Isinya |
|-------|--------|
| `/` | Hero "Kenalan sama BitSI" · 4 kartu divisi bidang (Web Dev/IoT/Jaringan/Server) · showcase karya · pengajar & pengurus · jadwal rutinan · galeri · testimoni · CTA Gabung |
| `/daftar` ⭐ | **Form registrasi member** (akun + auto-apply BitSI → pending approval ketua). *Fitur dipindah dari landing page — keputusan OQ-3* |
| `/login` | Login anggota |

### 👤 Member Area *(login + status disetujui)*

| Route | Isinya |
|-------|--------|
| `/app` | Dashboard: sapaan · rapat terdekat · kelas terdekat · pengumuman terbaru |
| `/app/rapat` | List rapat mendatang/lampau + detail agenda & notulen (read-only) |
| `/app/rapat/:id` | Detail + tombol **Scan Absensi** (jika window rapat aktif) + riwayat kehadiran pribadi |
| `/app/kelas` | Katalog kelas per divisi + jadwal rutin |
| `/app/kelas/:id` | Detail + daftar materi (buka/unduh) |
| `/app/profil` | Lihat/edit data diri, foto, link portofolio & Instagram |

> ℹ️ **Tagihan iuran/kas TIDAK ditampilkan di app ini** (keputusan rizz): anggota melihat status kas pribadinya melalui panel Laravel (akun + role masing-masing). Dashboard juga tidak menampilkan ringkasan iuran.

### 🛠 Pengurus BitSI *(role ketua/pengajar — scope komunitas BITSI)*

| Route | Isinya |
|-------|--------|
| `/app/pengurus/pendaftar` | Daftar pending → setujui/tolak |
| `/app/pengurus/presenter` | **Mode presenter QR**: fullscreen, QR berotasi otomatis tiap 60s (poll `GET /rapat/{id}/qr`) |
| `/app/pengurus/karya` | CRUD showcase proyek anggota |
| `/app/pengurus/kelas` | CRUD kelas + upload materi |

## 4. Keputusan Teknis (ADR ringkas)

| # | Keputusan | Alasan |
|---|-----------|--------|
| B1 | Vite + React + TypeScript strict + Tailwind + shadcn/ui | Default config; konsisten dengan ekosistem project lain |
| B2 | React Router (data router) + lazy load route per-area | Code splitting: public/member/pengurus bundle terpisah |
| B3 | TanStack Query untuk server state | Cache, refetch, loading/error state standar — cocok pola REST |
| B4 | Token Sanctum di localStorage + interceptor auto-logout 401 | Praktis untuk SPA mahasiswa; risiko XSS dimitigasi tanpa `dangerouslySetInnerHTML` |
| B5 | Guard route berbasis role+status keanggotaan | Pending → halaman "menunggu persetujuan"; non-member tidak bisa masuk `/app` |
| B6 | Scan absensi pakai library html5-qrcode (kamera HP) | Tanpa native dependency; fallback input manual token |
| B7 | Deploy Cloudflare Pages (config `deploy.react`) | Gratis, cepat, SPA redirect mudah |
| B8 | Env: `VITE_API_URL`, `VITE_BITSI_URL` (self), share OG | Konsisten pola env-based seperti landing page |

## 5. State Matrix Global (berlaku semua route data)

| Kondisi | Perilaku |
|---------|----------|
| Loading | Skeleton per-card, bukan spinner fullscreen |
| Empty | Ilustrasi ringan + pesan ramah + CTA relevan ("Belum ada rapat — cek lagi nanti") |
| Error API | Inline error + tombol retry; TIDAK crash seluruh app (error boundary per area) |
| 401 | Auto-redirect `/login` dengan pesan sesi habis |
| 403 | Halaman "Kamu tidak punya akses" + penjelasan syarat akses |
| Pending approval | Setelah login → layar khusus "Menunggu persetujuan ketua" + info kontak pengurus |

## 6. Success Metrics

1. Calon anggota bisa daftar mandiri < 2 menit tanpa bantuan
2. Absensi rapat via QR < 10 detik per orang antre
3. Materi kelas bisa diakses member kapan saja dari HP
4. Showcase karya jadi portofolio kolektif yang membanggakan & merekrut

## 7. Open Questions

1. Testimoni anggota di public home: statis (hardcode) atau dari backend?
   → Rekomendasi: **statis dulu**, upgrade ke backend kalau sering update.
2. Galeri dokumentasi BitSI: tarik dari modul galeri backend (`komunitas=BITSI`) atau folder statis?
   → Rekomendasi: **tarik dari backend** — sudah ada modulnya.
3. Apakah pengajar non-pengurus butuh akses upload materi? Saat ini asumsi: ya, via role pengajar pada kelas terkait.

---

*PRD terkait:* backend M11–M12 (`docs/prd/backend-api.md`) · US-23–24 (`docs/prd/stories.md`)
