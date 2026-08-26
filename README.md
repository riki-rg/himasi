# HIMSI UMKU — Ecosystem Development Guide (Universal)

> **Dokumen ini adalah pintu masuk utama untuk AI coding assistant maupun manusia yang baru bergabung mengerjakan project ini.**
> Terakhir diperbarui: 2026-08-25 · Pemilik: rizz (Mahasiswa SI UMKU)

---

## 🎯 Apa Project Ini?

Satu ekosistem digital untuk **HIMSI** (Himpunan Mahasiswa Sistem Informasi Universitas Muhammadiyah Kudus) beserta dua komunitas di bawahnya:

| # | Aplikasi | Stack | Peran |
|---|----------|-------|-------|
| 1 | **Backend API** | Laravel ^13.8 + Breeze (API mode) + Sanctum | Single source of truth — 12 modul |
| 2 | **Landing page** | Next.js App Router + TS strict + Tailwind v4 | Wajah resmi himpunan, konsumen endpoint `/publik/*` |
| 3 | **App BitSI** | React SPA (Vite) + TS strict + Tailwind + shadcn/ui | Komunitas tech: Web Dev · IoT · Jaringan · Server |
| 4 | **App Sibiner** | React SPA (Vite) — share `packages/ui` dengan BitSI | Komunitas literasi: bicara nalar & baca buku |

Frontend #2–#4 dibangun sebagai **monorepo pnpm workspace** (`apps/*` + `packages/ui`).

## 📁 Lokasi Kode & Dokumen

```
/home/rizz/Documents/projects/hima/     ← ROOT project (dokumentasi + frontend + backend)
├── README.md                           ← KAMU DI SINI
├── docs/
│   ├── prd/
│   │   ├── backend-api.md              ← PRD 12 modul + ADR D1–D8
│   │   ├── landing-page.md             ← PRD + ADR L1–L7
│   │   ├── bitsi-app.md                ← PRD + ADR B1–B8
│   │   ├── sibiner-app.md              ← PRD + ADR S1–S5
│   │   └── stories.md                  ← 24 user story (6 fase + epic H), AC Given/When/Then
│   ├── design/
│   │   ├── erd.md                      ← skema database lengkap + index strategy
│   │   ├── struktur-organisasi.md      ← definisi divisi/jabatan + spesifikasi seeder
│   │   └── wireframes/                 ← 16 wireframe + README index
│   └── api/openapi.yaml                ← kontrak REST (59 endpoint) — SUMBER KEBENARAN ENDPOINT
├── frontend/                           ← monorepo pnpm workspace
│   ├── apps/{landing,bitsi,sibiner}/
│   └── packages/ui/
└── backend/                            ← PROJECT LARAVEL (API) — fresh install, Breeze API mode + Sanctum
    └── app/, database/migrations/, ... ← Fase 1 selesai: skema ERD final + auth /api/v1/auth/*

/home/rizz/himsi                        ← LARAVEL LAMA (arsip, tidak dipakai — jangan disentuh)
```

## 🚦 URUTAN PEKERJAAN — AI HARUS MULAI DARI SINI

**Jangan asal ngoding. Ikuti urutan ini. Satu fase tuntas (quality gates pass) baru lanjut.**

### FASE 0 — Pemahaman Konteks (wajib sebelum menulis kode apa pun)

1. Baca dokumen ini sampai habis
2. Baca `docs/prd/backend-api.md` (PRD + ADR — keputusan desain SUDAH TERKUNCI, jangan ubah tanpa izin owner)
3. Baca `docs/design/erd.md` + `docs/design/struktur-organisasi.md`
4. Baca `docs/api/openapi.yaml`
5. ~~Inspeksi kondisi aktual `/home/rizz/himsi`~~ ✅ selesai — backend kini live di `backend/` (fresh install, skema final)

### FASE 1 — Fondasi Backend (stories US-01 s/d US-03)

Urutan konkret:

1. ~~Install Laravel Breeze **API mode** + Sanctum di `backend/`~~ ✅ Laravel 13.26 · prefix `api/v1` · problem+json RFC 7807
2. ~~Migration skema final sesuai ERD (fresh install → langsung final schema; expand-contract tidak diperlukan karena tanpa data legacy)~~ ✅ 24 tabel + amendment `users.status` enum(pending, aktif) disetujui owner
3. ~~Seeder struktur standar (definisi final di `docs/design/struktur-organisasi.md`)~~ ✅ 3 komunitas → 5 divisi → 16 jabatan + periode aktif
4. ~~Auth API: register (pending approval) / login / logout / me / password~~ ✅ `app/Http/Controllers/AuthController.php` + feature tests 22 pass
5. Role & permission per komunitas ✅ Gates (`admin-pusat`, `bendahara`, `sekretaris`, `pengurus-komunitas:{kode}`) dari `App\Services\RoleResolver` — validasi menyeluruh menyusul saat modul dibangun

### FASE 2 — Anggota (US-04–06) ✅ selesai

- ~~CRUD anggota + search/filter + pagination~~ ✅ `AnggotaController` · cap per_page 100 · filter q/status/angkatan/komunitas
- ~~Import/export xlsx-csv~~ ✅ openspout (streaming, tanpa ext-gd) · laporan `detail_gagal` per baris
- ~~Keanggotaan komunitas~~ ✅ apply mandiri pending · input manual ketua langsung disetujui · duplikat 409 · approve lintas komunitas 403

### FASE 3 — Struktur Organisasi + Konten Publik (US-07–13) ✅ selesai ⚡ *landing page bisa hidup*

- ~~CRUD periode/divisi/jabatan~~ ✅ periode baru otomatis mengarsipkan yang lama; periode arsip read-only (409)
- ~~Penugasan pengurus~~ ✅ duplikat 422; hapus penugasan tidak menyentuh data member
- ~~`GET /publik/struktur`~~ ✅ plain array per openapi · fallback periode arsip · filter `?komunitas=` · alumni tanpa akun tetap tampil
- ~~Artikel~~ ✅ draft→published + slug unik auto-suffix; publik list/detail by slug
- ~~Event & Pengumuman~~ ✅ `?mendatang=true`, batal tak tampil; pengumuman masa tayang + prioritas penting duluan
- ~~Galeri~~ ✅ album CRUD + multi-upload ≤30 foto ×5MB (openspout-free, Storage::) · cover otomatis foto pertama

### FASE 4 — Rapat & Presensi QR (US-14–16) ✅ selesai

- ~~CRUD rapat + peserta~~ ✅ qr_secret auto-generate & tidak pernah diekspos; sync member_ids on update
- ~~Notulen~~ ✅ teks + lampiran PDF ≤10MB via Storage::
- ~~Absensi QR rotasi 60 detik (ADR D1)~~ ✅ token `{id}|{window}|{hmac}` HMAC-SHA256, zero write DB per rotasi; mapping error 410 QrExpired · 409 SudahAbsen · 422 invalid; jendela aktif = hari-H mulai H-15m s.d. selesai+120m (`App\Services\QrPresensi`)
- ~~Rekap kehadiran~~ ✅ hitungan hadir/tidak/izin + persentase + rincian

### FASE 5 — Surat (US-17–19) ✅ selesai

- ~~Template penomoran otomatis (ADR D4)~~ ✅ render `{urut}/{romawi}/{tahun}` · counter atomik `lockForUpdate` · edit format tidak mengubah nomor lama
- ~~Arsip masuk & keluar~~ ✅ scan PDF ≤10MB · pencarian q nomor/perihal/pihak · keluar tanpa template 422
- ~~Alur status surat keluar~~ ✅ adjacent-only maju draft→review→disetujui→terkirim; mundur/lompat 409; audit trail tabel `surat_status_logs` (+ endpoint `GET /surat/{id}/logs`)
- 📌 Catatan amendment: ERD +openapi butuh sinkronisasi kecil — tabel `surat_status_logs` & `PUT /surat/templates/{id}` (kebutuhan eksplisit AC US-17/19) — menunggu update yaml oleh owner

### FASE 6 — Keuangan (US-20–22) + Showcase Karya & Kelas (US-23–24) ✅ selesai — BACKEND TUNTAS

- ~~Transaksi kas + bukti~~ ✅ nominal DECIMAL(12,2) → string di JSON; bukti foto ≤5MB; kategori auto-resolve `firstOrCreate`
- ~~Rekap~~ ✅ saldo = Σmasuk−Σkeluar; breakdown per bulan/kategori; akses bendahara full · pengurus lain agregat saja (ADR D2)
- ~~Laporan export~~ ✅ xlsx/csv berisi transaksi+total+saldo (flag amendment: endpoint `/kas/export` belum ada di openapi)
- ~~Iuran & tagihan otomatis~~ ✅ POST /iuran generate tagihan anggota aktif komunitas/HIMSI semua; lunasi atomik → transaksi kas pemasukan otomatis + kas_id terisi
- ~~Showcase karya~~ ✅ pengurus scoped komunitasnya (lintas → 403); admin bebas; publik published-only + nama pembuat
- ~~Kelas & materi~~ ✅ materi gated member disetujui (non-member 403); publik list tanpa materi; upload file ≤10MB / link; pengajar via penugasan divisi
- 📌 Seed baru: 6 kategori kas standar (Iuran/Sponsor/Donasi/Konsumsi/Transportasi/Perlengkapan)

### FASE 7+ — Frontend ✅ scaffold tuntas (v1 berfungsi, siap iterasi wireframe lanjutan)

1. ~~Scaffold monorepo pnpm workspace~~ ✅ `apps/{landing,bitsi,sibiner}` + `packages/ui` · Biome + TS strict + vitest
2. ~~Landing page~~ ✅ Next.js App Router · home sesuai wireframe (`home.md`) · ISR 60s · artikel list/detail · agenda · galeri · struktur org chart · **anti-blank ADR L7** (tiap section degrade mandiri) · CTA gabung → env BitSI (OQ-3)
3. ~~BitSI~~ ✅ `/daftar` lengkap per `bitsi-daftar.md` (stepper, error inline RFC 7807, layar sukses pending) · login (+error mapping) · `/app` guarded (sidebar desktop/bottom-tab mobile) · dashboard hero rapat terdekat + kelas + pengumuman
4. ~~Sibiner~~ ✅ Warm Library tokens (krem × forest × serif) · home rak buku dari `publik/proyeks?komunitas=SIBINER` · login · `/app` daftar diskusi — mirror pola BitSI (bukti S3)
- 📌 Komponen struktural di `@himsi/ui`; yang membedakan antar-app hanya design tokens (@theme) — prinsip wireframes README #5
- 📌 Quality gates frontend: `pnpm lint` + `typecheck` + `test` (7/7) + `build` hijau semua

#### Iterasi wireframe lanjutan ✅

- ~~`bitsi-rapat-detail.md`~~ ✅ list rapat mendatang/lampau + detail 3-fase (terjadwal → berlangsung → selesai/notulen); **scan absensi** html5-qrcode frame sudut cobalt + fallback kode manual; mapping error: 410 "QR sudah ganti" · 409 ramah tutup scanner · 422 inline; vibrate sukses; peserta collapse dgn status kehadiran
- ~~`bitsi-presenter-qr.md`~~ ✅ layar pilih rapat → fullscreen QR hitam-putih murni (`react-qr-code`); rotasi refresh t=52s via ticker 1s; progress bar sisa detik; live counter poll rekap 10s + nama baru (inisial privasi); pause ⏸ sembunyikan QR; Wake Lock; konfirmasi keluar inline + ringkasan; kode manual full payload ditampilkan mono kecil
- ~~`bitsi-profil.md`~~ ✅ NIM readonly; save-bar sticky saat dirty; foto ≤5MB; ganti password inline
- ~~`bitsi-pengurus-pendaftar.md`~~ ✅ tabs pending/disetujui/ditolak; approve + **undo toast 5 detik**; race 409 → auto-refresh
- ~~`bitsi-kelas.md`~~ ✅ katalog kartu per divisi + detail materi unduh/buka (urutan sesi terjaga)
- ~~`galeri-album.md`~~ ✅ masonry columns + lightbox deep-linkable `?foto=N`, keyboard ←→ESC, swipe mobile, preload ±1
- ~~Amendment backend~~ ✅ **openapi.yaml v1.1.0 tersinkron penuh**: GET /keanggotaan · PUT /surat/templates/{id} · GET /kas/export · GET /surat/{id}/logs · /auth/me tingkat+komunitas_kode · daftar_pada · konvensi PATCH=alias PUT

#### Iterasi wireframe tahap akhir ✅ — 16/16 TERCAKUP

- ~~`bitsi-pengurus-kelola.md`~~ ✅ kelola karya: toolbar search+filter status, form dialog lengkap (chips teknologi multi-input, pembuat dari /anggota, thumbnail ≤5MB, publish/unpublish, hapus) · kelola kelas: buat kelas + daftar materi dgn tambah file/link, **urutan panah ⬆⬇**, edit judul inline, hapus
- ~~`sibiner-member.md`~~ ✅ `/app/diskusi/:id` mirror rapat-detail 3-fase + scan absen (ScannerOverlay kini share di `@himsi/ui`) · `/app/bacaan` katalog + rangkuman unduh; guard token + redirect kembali pasca login
- ~~`landing-struktur.md`~~ ✅ periode selector dropdown + badge ARSIP amber + tab komunitas Semua/HIMSI/BitSI/Sibiner via searchParams + anchor per divisi + jabatan kosong "akan diisi"

## 📜 Aturan Main (WAJIB dipatuhi AI)

### Kontrak & Desain
- **Endpoint mengikuti `docs/api/openapi.yaml`** — path, method, schema, status code. Kalau spec perlu berubah: usulkan dulu, update spec, baru implement
- **Error format: RFC 7807 problem+json** dengan extension `errors{}` untuk 422
- **Pagination gaya Laravel**: `{data, meta{current_page,last_page,total}, links}`
- **ADR terkunci** (D1–D8, L1–L7, B1–B8, S1–S5 di tiap PRD): QR rotasi HMAC tanpa write DB · kas read-only setelah periode arsip · storage lokal via abstraction `Storage::` · dsb. Jangan implementasi bertentangan dengan ADR
- Bahasa: nama field/kode EN, konten & pesan user-facing **Bahasa Indonesia**

### Teknis
- Backend: PHP Laravel ^13.8 · model pakai atribut `#[Fillable]` + method `casts()` (konvensi `backend/app/Models` — atribut bawaan Laravel 13)
- Frontend: TypeScript **strict**, TanStack Query (BitSI/Sibiner), komponen shadcn-style di `packages/ui`
- Semua URL eksternal via env (`NEXT_PUBLIC_API_URL`, `VITE_API_URL`, dst.) — zero hardcoded domain
- Money selalu `DECIMAL(12,2)` / string di JSON — **jangan float**
- Upload: foto ≤5MB, PDF ≤10MB, via `Storage::` abstraction

### Migration Safety
- Expand-contract: tambah kolom nullable → backfill → baru constrain/drop
- Setiap migration punya `down()` yang benar
- Dilarang destructive change pada data existing tanpa persetujuan owner

### Quality Gates (sebelum claim "selesai")
1. `vendor/bin/pint` / lint bersih
2. `php artisan test` pass (PHPUnit — tulis test feature per endpoint)
3. Manual verify endpoint sesuai contoh openapi.yaml
4. Frontend: `lint` + `typecheck` + `test` + `build` semua hijau

## ✅ Definition of Done per User Story

- [ ] Semua acceptance criteria (GWT) di `docs/prd/stories.md` terpenuhi
- [ ] Endpoint terdaftar di openapi.yaml & implementasinya konsisten
- [ ] Test feature menutup happy path + minimal satu error path
- [ ] State matrix wireframe terhormat (loading/empty/error tidak polos kosong)
- [ ] Quality gates pass

## 🔑 Fakta Kunci yang Sering Terlupakan

1. Tabel `kas` existing **belum punya kolom nominal** — wajib ditambahkan di Fase 1 refactor
2. Kolom `jabatan` string di `members` adalah warisan lama → digantikan sistem penugasan dinamis
3. BitSI = program di bawah **Divisi Pengembangan Diri**; Sibiner = di bawah **Divisi Organisasi** (bukan organisasi paralel)
4. Registrasi mandiri hanya ada di app **BitSI**; anggota Sibiner di-input manual pengurus
5. Tagihan iuran TIDAK tampil di app komunitas — cukup lewat panel Laravel (keputusan owner)
6. Domain belum dibeli — semua URL via env; struktur rencana: `himsiumku.[domain]` + subdomain `bitsi.` `sibiner.` `api.`

## 💬 Cara Bertanya ke Owner (rizz)

Kalau ada keputusan di luar cakupan dokumen: **berhenti, tanyakan** — jangan mengarang sendiri. Konteks pertanyaan selalu sertakan: fase/story mana, dokumen mana yang sudah dibaca.
