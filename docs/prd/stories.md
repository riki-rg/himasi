# User Stories — Backend API HIMSI UMKU

> Status: Draft v1 · Tanggal: 2026-08-25 · Sumber: PRD v1.2 · ERD v1 · OpenAPI 1.0-draft
> Kompleksitas: S (<1 hari) · M (1–3 hari) · L (>3 hari) — estimasi relatif, bukan jam presisi
> Urutan = prioritas implementasi (dependency-aware, quick win di atas)

---

## Epic A · Fondasi

### US-01 — Master Data Periode & Komunitas `S`
Sebagai developer, aku butuh tabel periode & komunitas terisi seed data agar semua modul lain punya referensi.

**Acceptance Criteria:**
- Given seed dijalankan → When cek DB → Then ada 3 komunitas (`HIMSI`, `BITSI`, `SIBINER`) dan 1 periode berstatus `aktif`
- Given periode aktif ada → When request `GET /api/v1/publik/struktur` tanpa param → Then pakai periode aktif sebagai default

### US-02 — Auth: Register, Login, Logout, Me `M`
Sebagai mahasiswa, aku bisa mendaftar mandiri dan login agar dapat mengakses fitur member.

**AC:**
- Given NIM belum terdaftar → When `POST /auth/register` dengan data valid → Then akun dibuat berstatus menunggu approval, response 201
- Given email terdaftar & akun disetujui → When login kredensial benar → Then 200 + token Bearer dikembalikan
- Given akun masih pending → When login → Then HTTP 423 problem+json "akun menunggu persetujuan"
- Given token valid → When `POST /auth/logout` → Then token dicabut, request berikutnya 401

### US-03 — Role & Permission per Komunitas `L`
Sebagai sistem, aku harus membedakan akses admin / pengurus komunitas / bendahara / sekretaris / anggota sesuai PRD §3.

**AC:**
- Given user adalah ketua BitSI → When akses endpoint rapat milik BitSI → Then diizinkan; When akses rapat milik Sibiner → Then 403 problem+json
- Given user bendahara → When `GET /kas/{id}` → Then diizinkan; When anggota biasa → Then 403; When anggota lihat iuran sendiri → Then diizinkan (ADR D2)
- Given admin pusat → When akses endpoint apa pun → Then diizinkan

## Epic B · Anggota

### US-04 — CRUD Anggota + Search/Filter `M`
Sebagai pengurus, aku mengelola data anggota lengkap (termasuk link portofolio & Instagram) agar database internal rapi.

**AC:**
- Given form valid → When `POST /anggota` → Then 201, NIM unik tervalidasi (duplikat → 422)
- Given 150 anggota → When `GET /anggota?q=rizz&angkatan=2021&status=aktif` → Then hasil terfilter, terpaginasi Laravel meta, ≤100/per_page
- Given anggota dipilih → When update `link_portofolio` → Then tersimpan dan muncul di detail

### US-05 — Import/Export Excel-CSV `M`
Sebagai admin, aku import data anggota massal dari Excel agar tidak input satu-satu (PRD M7).

**AC:**
- Given file xlsx 120 baris valid → When `POST /anggota/import` → Then `{berhasil: 120, gagal: 0}`
- Given file berisi 2 baris NIM duplikat → When import → Then berhasil sisanya, `detail_gagal` menyebut baris & alasan
- When `GET /anggota/export?format=xlsx` → Then file unduhan berisi seluruh kolom anggota

### US-06 — Keanggotaan Komunitas (Apply + Approval) `S`
Sebagai mahasiswa, aku daftar masuk BitSI/Sibiner lalu ketua komunitas yang menyetujui (ADR D6).

**AC:**
- Given member aktif → When apply ke BITSI → Then pivot dibuat berstatus `pending`
- Given pendaftaran pending → When ketua BitSI approve via `PATCH /keanggotaan/{id}` → Then status `disetujui` + `disetujui_pada` terisi
- Given member sudah tergabung → When apply lagi ke komunitas sama → Then 409 problem+json

## Epic C · Struktur Organisasi

### US-07 — CRUD Divisi & Jabatan `S`
Sebagai admin, aku susun struktur tiap periode: divisi berisi jabatan.

**AC:**
- Given periode aktif → When `POST /periodes/{id}/divisi` nama "Divisi IT" → Then 201
- Given divisi ada → When `POST /divisi/{id}/jabatan` "Ketua" tingkat utama → Then 201
- Given periode sudah `arsip` → When coba tambah divisi → Then 403/409 (read-only, ADR D3)

### US-08 — Penugasan Pengurus `S`
Sebagai admin, aku tunjuk anggota menduduki jabatan agar org chart terbentuk.

**AC:**
- Given anggota + jabatan valid → When `POST /penugasan` → Then 201 dan anggota muncul di struktur
- Given penugasan duplikat (member+jabatan+periode sama) → When submit lagi → Then 422
- When `DELETE /penugasan/{id}` → Then anggota hilang dari struktur, datanya tidak ikut terhapus

### US-09 — Org Chart Publik `S` ⚡ *quick win*
Sebagai pengunjung landing page, aku melihat struktur organisasi HIMSI/BitSI/Sibiner terbaru.

**AC:**
- Given penugasan periode aktif ada → When `GET /publik/struktur` → Then tree divisi→jabatan→pengurus (nama, foto, IG) terurut `urutan`
- When param `komunitas=BITSI` → Then hanya struktur BitSI
- Given tidak ada periode aktif → When request → Then fallback ke periode arsip terakhir

## Epic D · Konten Publik ⚡ *semua quick win*

### US-10 — Artikel: Draft → Published + Konsumsi Publik `M`
Sebagai pengurus, aku menulis artikel; sebagai pengunjung, aku membacanya di landing page.

**AC:**
- Given artikel draft → When `POST /artikels` → Then tersimpan; When `GET /publik/artikels` → Then draft TIDAK muncul
- Given artikel published → When `GET /publik/artikels/{slug}` → Then detail + author muncul
- Given 50 artikel published → When list → Then paginasi meta benar, filter `kategori` bekerja

### US-11 — Agenda/Event Publik `S`
Sebagai pengunjung, aku melihat jadwal kegiatan mendatang per komunitas.

**AC:**
- Given event mulai besok → When `GET /publik/events?mendatang=true` → Then event muncul
- When `komunitas=SIBINER` → Then hanya event Sibiner
- Given event status batal → Then tidak muncul di publik

### US-12 — Pengumuman Aktif `S`
**AC:** pengumuman dalam masa tayang muncul di `/publik/pengumumans`; kadaluarsa tidak; prioritas `penting` bisa difilter/diurut duluan.

### US-13 — Galeri Album & Foto `S`
**AC:** album multi-foto dengan urutan stabil; upload foto ≤5MB dioptimalkan; album bisa terkait event; detail album menyertakan semua fotonya.

## Epic E · Rapat & Presensi

### US-14 — CRUD Rapat + Peserta `M`
Sebagai pengurus, aku jadwalkan rapat, tentukan peserta, tulis notulen.

**AC:**
- Given payload valid → When `POST /rapat` dengan `member_ids` → Then rapat dibuat, qr_secret tergenerate otomatis (tidak pernah diekspos di response)
- When `PUT /rapat/{id}/notulen` + lampiran PDF → Then tersimpan, lampiran path terisi
- Given rapat milik BitSI → When anggota non-BitSI akses detail → Then 403

### US-15 — Absensi QR Rotasi 60 Detik `M`
Sebagai peserta, aku scan QR yang ditampilkan pengurus agar kehadiran tercatat anti-titip-absen (ADR D1).

**AC:**
- Given rapat dijadwalkan hari ini → When pengurus `GET /rapat/{id}/qr` → Then payload HMAC(secret, window60) + expires_in ≤60, TANPA write DB per rotasi
- Given payload valid dalam window → When member `POST /rapat/{id}/absen` → Then kehadiran `hadir` + waktu tercatat
- Given payload dari screenshot 2 menit lalu → When absen → Then 410 QrExpired
- Given member sudah absen → When scan lagi → Then 409 SudahAbsen
- Given payload HMAC dari rapat lain → When absen → Then 422 token invalid

### US-16 — Rekap Kehadiran `S`
Sebagai pengurus, aku melihat statistik kehadiran untuk evaluasi anggota.

**AC:** `GET /rapat/{id}/rekap` mengembalikan hitungan hadir/tidak/izin + persentase; rekap antar-rapat per member tersedia via query member_id.

## Epic F · Surat (Sekretariat)

### US-17 — Template Penomoran Otomatis `M`
Sebagai sekretaris, aku atur format nomor surat keluar per jenis agar penomoran konsisten (ADR D4).

**AC:**
- Given template `{urut}/HIMSI/UMKU/{romawi}/{tahun}` counter 0 → When surat keluar pertama dibuat → Then nomor `001/HIMSI/UMKU/VIII/2026`, counter menjadi 1
- Given dua pembuatan bersamaan → Then nomor tetap unik (lock atomik), unique `(nomor_surat, periode_id)`
- Given format diedit → Then surat lama tidak berubah nomornya

### US-18 — Arsip Surat Masuk & Keluar `S`
**AC:** surat masuk/keluar tersimpan dengan scan PDF ≤10MB; pencarian q mencocokkan nomor/perihal/pihak; filter jenis+periode bekerja; surat keluar tanpa template → 422.

### US-19 — Alur Status Surat Keluar `S`
**AC:** transisi hanya maju draft→review→disetujui→terkirim; mundur/melompat → 409; setiap transisi mencatat siapa & kapan.

## Epic G · Keuangan (Bendahara)

### US-20 — Transaksi Kas + Bukti `M`
Sebagai bendahara, aku catat pemasukan/pengeluaran lengkap nominal & bukti nota (fix audit PRD).

**AC:**
- Given transaksi valid → When `POST /kas` nominal 150000 → Then tersimpan DECIMAL(12,2) presisi benar (tidak float)
- Given anggota non-bendahara → When `GET /kas` → Then 403; When `GET /kas/rekap?periode_id=` → Then boleh (hanya agregat, ADR D2)
- Given saldo = Σmasuk−Σkeluar → When rekap → Then angka cocok dengan manual count

### US-21 — Laporan Export `M`
Sebagai bendahara, aku export laporan bulanan siap presentasi dalam hitungan menit (success metric #4).

**AC:** export xlsx/pdf per periode atau rentang tanggal berisi transaksi + total + saldo; breakdown per kategori tersedia.

### US-22 — Iuran & Tagihan Otomatis `L`
Sebagai bendahara, aku bikin iuran bulanan yang otomatis menagih semua anggota terkait.

**AC:**
- Given iuran "Kas Feb" 25rb untuk member aktif (30 orang) → When `POST /iuran` → Then 30 tagihan berstatus `belum`
- When lunasi tagihan → Then status `lunas`, `kas_id` terisi, transaksi kas pemasukan otomatis dibuat (atomik — gagal satu gagal semua)
- Given ringkasan iuran → When `GET /iuran/{id}` → Then `{total_tagihan, lunas, belum}` akurat

## Epic H · Showcase Karya & Kelas *(tambahan dari planning App BitSI)*

### US-23 — Showcase Karya per Komunitas `M`
Sebagai anggota BitSI, karyaku (proyek web/IoT/server) dipamerkan di app komunitas agar memotivasi calon anggota.

**AC:**
- Given pengurus komunitas → When `POST /proyeks` dengan thumbnail + link demo/repo + teknologi → Then 201 berstatus draft
- When publish → Then muncul di `GET /publik/proyeks?komunitas=BITSI` lengkap dengan nama pembuat
- Given proyek milik komunitas lain → When akses CRUD dari pengurus BitSI → Then 403
- Given Sibiner nanti butuh → When pakai endpoint sama dengan komunitas=SIBINER → Then bekerja tanpa modifikasi kode (scope by design)

### US-24 — Kelas, Pengajar & Materi `M`
Sebagai member BitSI, aku melihat katalog kelas per divisi dan mengunduh materi sesi agar belajar tidak berhenti di kelas.

**AC:**
- Given kelas "Web Dev Dasar" dengan pengajar (penugasan) + jadwal rutin "Sabtu 16.00" → When publik lihat daftar kelas → Then nama+divisi+pengajar+jadwal tampil (tanpa materi)
- Given member BitSI disetujui → When buka detail kelas → Then daftar materi terurut bisa dibuka/diunduh
- Given non-member / belum disetujui → When coba akses materi → Then 403 problem+json

---

## Ringkasan Prioritas

| Fase | Stories | Total kompleksitas | Nilai |
|------|---------|-------------------|-------|
| 1 — Fondasi | US-01–03 | M+S+L | Semua modul bergantung ini |
| 2 — Anggota | US-04–06 | M+M+S | Database internal hidup |
| 3 — Struktur + Konten ⚡ | US-07–13 | S×5 + M | **Landing page langsung hidup** |
| 4 — Rapat | US-14–16 | M+M+S | Administrasi harian digital |
| 5 — Surat | US-17–19 | M+S+S | Arsip digital sekretariat |
| 6 — Keuangan | US-20–22 | M+M+L | Laporan bendahara instan |

> 💡 Learning: Urutan fase ini disengaja — fase 3 diletakkan sebelum modul internal supaya ada *visible progress* cepat (landing page bisa demo walau modul internal belum jadi). Bagi motivasi tim kemahasiswaan, demo yang bisa dilihat mata itu bahan bakar.
