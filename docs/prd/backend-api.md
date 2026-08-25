# PRD — Backend API Website HIMSI UMKU

> Status: Draft v1.1 · Tanggal: 2026-08-25 · Pemilik: rizz
> Lokasi kode backend: `backend/` (monorepo hima) · *Historis: awalnya `/home/rizz/himsi` lalu dimigrasi fresh* · Semua artifact design disimpan di `/home/rizz/Documents/projects/hima/docs/`
> Lingkup: Satu backend Laravel (API) melayani 3 client — landing page Next.js, aplikasi React BitSI, aplikasi React Sibiner.

---

## 1. Latar Belakang & Problem Statement

HIMSI (Himpunan Mahasiswa Sistem Informasi) Universitas Muhammadiyah Kudus beserta komunitas di bawahnya — **BitSI** (Web dev, IoT, Jaringan, Server) dan **Sibiner** (bicara nalar & literasi) — mengelola administrasi organisasi secara manual:

- Data anggota tersebar di spreadsheet/WA group, sulit dicari antar periode kepengurusan
- Absensi rapat pakai kertas, rawan hilang & sulit direkap
- Surat masuk/keluar belum terarsip digital
- Laporan keuangan butuh waktu lama disusun ulang dari catatan manual
- Konten publik (artikel, agenda, galeri) tidak punya rumah resmi

**Problem:** informasi organisasi tidak terpusat, tidak tervalidasi, dan tidak berkelanjutan antar periode kepengurusan.

## 2. Goals / Non-goals

### Goals
1. **Satu API terpusat** (Laravel) yang melayani semua client — data konsisten, sekali input banyak dipakai.
2. **Digitalisasi administrasi internal:** anggota (CRUD + import), rapat (jadwal, notulen, absensi QR), surat (arsip digital + penomoran otomatis), keuangan (transaksi, iuran, laporan).
3. **Manajemen konten publik:** artikel, agenda/event, galeri, pengumuman — dikelola pengurus tanpa perlu developer.
4. **Struktur organisasi dinamis** per periode: divisi + jabatan HIMSI, BitSI, Sibiner — bisa ditampilkan di landing page & aplikasi komunitas.
5. Multi-level role: publik → anggota → pengurus/divisi → admin, per-komunitas.

### Non-goals (out of scope versi ini)
- Mobile app native (Android/iOS)
- Payment gateway otomatis (iuran dicatat manual oleh bendahara dulu)
- Chat/forum internal realtime
- Integrasi WhatsApp API otomatis (pengingat lewat email/in-app dulu)
- Sistem voting/pemilihan ketua online

## 3. User & Role

| Role | Akses |
|------|-------|
| Publik (tanpa login) | Baca artikel, agenda, galeri, pengumuman, struktur organisasi (via landing page Next.js) |
| Anggota | Profil sendiri, daftar ikut komunitas, lihat jadwal rapat komunitasnya, scan absensi |
| Pengurus (per komunitas/divisi) | CRUD konten & administrasi lingkupnya: anggota komunitas, rapat, surat |
| Bendahara | Transaksi keuangan, iuran, laporan |
| Sekretaris | Surat masuk/keluar, notulen |
| Admin/Superadmin | Semua modul, kelola pengurus & periode kepengurusan |

> Catatan: satu orang bisa punya beberapa peran (mis. Ketua BitSI sekaligus anggota Sibiner).

## 4. Modul & Fitur (ringkasan)

### M1 · Auth & Role
- Login/logout API token (Sanctum, SPA-ready untuk 3 origin berbeda)
- Registrasi mandiri (open registration, menunggu approval admin)
- Import member massal (Excel/CSV) oleh admin
- Manajemen role & permission per komunitas

### M2 · Struktur Organisasi
- Entitas: periode kepengurusan → struktur (HIMSI/BitSI/Sibiner) → divisi → jabatan → penugasan anggota
- API publik read-only untuk ditampilkan di landing page & aplikasi komunitas
- Riwayat kepengurusan per periode

### M3 · Artikel/Publikasi
- CRUD artikel (judul, isi rich-text, cover, kategori, tag)
- Alur draft → published, atribut penulis
- API publik list/detail + pagination

### M4 · Agenda/Event
- CRUD event (judul, deskripsi, tanggal mulai/selesai, lokasi, poster)
- Penanda event komunitas mana (HIMSI/BitSI/Sibiner)
- Opsional pendaftaran peserta event

### M5 · Galeri
- Album + multi-upload foto, caption
- Terkait event (opsional)

### M6 · Pengumuman
- CRUD pengumuman singkat (banner/info), prioritas & masa tayang

### M7 · Manajemen Anggota
- CRUD lengkap: NIM, nama, prodi, angkatan, email, no HP, alamat, foto
- **Data tambahan (fitur):** link portofolio + link Instagram per anggota
- Status: aktif / nonaktif / alumni
- Keanggotaan komunitas (bisa ganda: BitSI + Sibiner)
- Import/export Excel-CSV, pencarian & filter

### M8 · Manajemen Rapat
- CRUD rapat: judul, agenda, tanggal-jam, tempat/link online, penyelenggara (divisi/komunitas)
- Peserta rapat (dari anggota terkait)
- **Absensi QR**: backend generate token unik per sesi, member scan → tercatat hadir
- Notulen + lampiran file
- Rekap kehadiran per anggota/periode

### M9 · Manajemen Surat (Sekretariat)
- Surat masuk: nomor, tanggal terima, pengirim, perihal, upload scan/PDF, disposisi
- Surat keluar: penomoran otomatis format `001/HIMSI/UMKU/VIII/2026`, jenis (proposal, undangan, SP, sertifikat, dll), alur status draft → review → disetujui → terkirim
- Arsip searchable per jenis & periode

### M10 · Manajemen Keuangan (Bendahara)
- Transaksi pemasukan/pengeluaran + kategori + nominal + bukti (foto nota)
- Kas/iuran: tagihan per anggota, status lunas/belum
- Laporan per periode/event, export Excel/PDF
- Saldo real-time (global / per divisi-komunitas)

### M11 · Showcase Karya *(ditambahkan saat planning App BitSI)*
- CRUD proyek anggota: judul, slug, deskripsi, thumbnail, link demo, link repo, teknologi (array), pembuat (member_id), divisi terkait
- Scope `komunitas_id` — dipakai ulang Sibiner (mis. review buku sebagai "karya")
- Status draft → published; API publik list/detail per komunitas untuk ditampilkan di app komunitas

### M12 · Kelas & Materi *(ditambahkan saat planning App BitSI)*
- CRUD `kelass`: nama, deskripsi, divisi terkait, pengajar (penugasan), jadwal rutin (hari/jam), scope komunitas
- CRUD `materis`: judul, tipe (file upload / link eksternal), urutan per kelas
- Member komunitas terkait bisa akses katalog + unduh materi; publik hanya lihat daftar kelas

## 5. Scope In / Out

| In scope (versi ini) | Out of scope |
|---|---|
| Seluruh modul M1–M10 | Payment gateway otomatis |
| REST API JSON + Sanctum auth | GraphQL |
| Upload file lokal/storage sederhana | CDN/cloud storage berbayar |
| Notifikasi in-app + email dasar | WhatsApp API |
| Seed data awal + dokumentasi endpoint | Frontend implementation (PRD terpisah) |

## 6. Success Metrics

**Esensi: seluruh administrasi organisasi mudah dibaca, mudah diakses, dan lebih termodernisasi.**

1. Pengurus mampu update konten (artikel/agenda/galeri/pengumuman) **tanpa bantuan developer**
2. Data anggota & rekap kehadiran rapat 100% tercatat di sistem (tidak lagi kertas/spreadsheet)
3. Surat terarsip digital dan dapat dicari < 1 menit
4. Bendahara menyusun laporan keuangan bulanan dalam hitungan menit (export siap pakai)
5. API dipakai nyaman oleh 3 frontend berbeda tanpa duplikasi logika data

## 7. Audit Kondisi Existing Code (2026-08-25)

Project `/home/rizz/himsi` sudah ada (Laravel ^13.8, **belum install Breeze**) dengan 3 migration custom:

| Tabel existing | Gap terhadap PRD |
|---|---|
| `members` | Belum ada: prodi, foto, link portofolio, link Instagram, relasi komunitas; kolom `jabatan` (string) bentrok dengan konsep struktur dinamis → refactor |
| `kas` | ⚠️ **Tidak ada kolom nominal uang**, bukti transaksi, relasi periode |
| `rapats` + `rapat_member` | Sudah solid; tambah: token QR, jam, lampiran notulen |

Modul lain (auth API, struktur org, konten publik, surat) belum dimulai.

## 8. Keputusan Desain (ADR ringkas)

Format: konteks → keputusan → konsekuensi.

| # | Konteks | Keputusan | Konsekuensi |
|---|---------|-----------|-------------|
| D1 | Token QR absensi rawan di-share | Token berotasi tiap ±60 detik; absen hanya valid saat jendela rapat aktif. Tanpa validasi GPS (overkill) | Pengurus menampilkan QR live; kecurangan tetap mungkin tapi effort penipuan naik signifikan |
| D2 | Data keuangan sensitif | Bendahara+Admin full access · pengurus lain rekap agregat · anggota hanya tagihan iuran sendiri | Perlu permission granular di modul kas |
| D3 | pergantian periode kepengurusan | Periode ditutup → data lama **read-only** (arsip searchable). Periode baru mulai bersih. Alumni tetap bisa login lihat riwayat | Butuh konsep `periode` sebagai entitas + flag arsip |
| D4 | Format nomor surat bisa berubah per periode | Format (`{urut}/HIMSI/UMKU/{romawi}/{tahun}`) + counter tersimpan di DB, editable admin | Bukan hardcode; perlu tabel template surat |
| D5 | Kapasitas & portabilitas media | Storage lokal Laravel dulu: 5 MB/foto, 10 MB/PDF, optimasi gambar on-upload. Via abstraction `Storage::` agar swap S3/R2 mudah nanti | Tanpa biaya awal; backup lokal harus disiplin |
| D6 | Siapa approve registrasi | Akun baru → admin pusat. Masuk komunitas (BitSI/Sibiner) → ketua komunitas terkait | Alur approval dua lapis, status pending per tahap |
| D7 | 3 frontend beda origin | Sanctum SPA mode + whitelist `stateful_domains`. Struktur domain disepakati: `himsiumku.[domain]` (landing) · `bitsi.` · `sibiner.` · `api.` — domain sendiri dibeli saat mendekati produksi; dev pakai `*.vercel.app`/`*.netlify.app` (lihat PRD landing OQ-1) | Konfigurasi CORS terpusat via env; ganti saat domain fix |
| D8 | Email verifikasi & pengingat | SMTP hosting/log gratis via abstraction `Mail::`; swap ke Resend/Mailtrap hanya ubah `.env` | Tanpa biaya awal, desain tetap fleksibel |

> 💡 Learning: ADR (Architecture Decision Record) tidak harus formal — satu baris konteks + keputusan + konsekuensi sudah cukup untuk mengingatkan tim (atau dirimu sendiri 6 bulan lagi) *kenapa* pilihan ini dibuat.

---

*Lanjutan natural dokumen ini:* ERD lengkap (`docs/design/erd.md`) → implementasi migration → API spec (`docs/api/openapi.yaml`).
