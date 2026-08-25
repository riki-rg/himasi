# PRD — Aplikasi Sibiner (React SPA)

> Status: Draft v1 · Tanggal: 2026-08-25 · Pemilik: rizz
> Komunitas: **Sibiner — Sistem Informasi Bicara Nalar dan Literasi** (membaca buku)
> Berada di bawah **Divisi Organisasi** HIMSI UMKU
> Konsumen endpoint Backend API · domain rencana: `sibiner.himsiumku.[domain]`

---

## 1. Latar Belakang

Sibiner butuh rumah digital untuk memperkenalkan komunitas literasinya dan melayani anggotanya: jadwal diskusi buku, presensi, koleksi review, serta materi rangkuman. Semua data dari backend HIMSI UMKU yang sudah dirancang *scoped per komunitas* — aplikasi ini membuktikan desain tersebut dengan memakai ulang modul yang sama seperti BitSI.

## 2. Keunikan vs BitSI

| Aspek | BitSI | Sibiner |
|-------|-------|---------|
| Keanggotaan | Daftar mandiri via `/daftar` ⭐ | **Khusus anggota HIMSI** — di-input manual oleh ketua Divisi Organisasi/admin; tanpa form daftar |
| Modul karya (M11) | Showcase proyek web/IoT | **Rak Buku**: review & koleksi bacaan |
| Modul kelas-materi (M12) | Kelas coding per divisi | **Sesi diskusi buku + rangkuman** |
| Kas | Di panel Laravel (keputusan rizz) | Sama |

## 3. Halaman & Route

### 🌐 Public *(memperkenalkan Sibiner)*

| Route | Isinya |
|-------|--------|
| `/` | Hero "Kenalan sama Sibiner" · **Rak Buku Kami** (grid review: cover, judul, rating, reviewer) · profil pengurus (Divisi Organisasi) · jadwal diskusi rutin · galeri · CTA gabung → kontak pengurus/IG |
| `/login` | Login anggota |

### 👤 Member Area *(login, keanggotaan di-input pengurus)*

| Route | Isinya |
|-------|--------|
| `/app` | Dashboard: sapaan · diskusi terdekat · sesi terdekat · pengumuman |
| `/app/diskusi` | List diskusi/rapat mendatang-lampau + detail agenda & notulen |
| `/app/diskusi/:id` | Detail + tombol Scan Absensi (saat window aktif) + riwayat kehadiran pribadi |
| `/app/bacaan` | Katalog bacaan/sesi + unduh rangkuman (M12) |
| `/app/profil` | Lihat/edit data diri |

> ℹ️ Tanpa fitur kas — status iuran dilihat lewat panel Laravel (keputusan rizz).

### 🛠 Pengurus Sibiner *(ketua Divisi Organisasi / role pengurus SIBINER)*

| Route | Isinya |
|-------|--------|
| `/app/pengurus/anggota` | Tambah/hapus anggota (dari pool anggota HIMSI aktif) |
| `/app/pengurus/presenter` | Mode presenter QR fullscreen berotasi 60s |
| `/app/pengurus/rak-buku` | CRUD review/koleksi buku (M11) |
| `/app/pengurus/sesi` | CRUD sesi diskusi + upload rangkuman (M12) |

## 4. Keputusan Teknis (ADR ringkas)

| # | Keputusan | Alasan |
|---|-----------|--------|
| S1 | **Monorepo pnpm workspace** bersama landing page & app BitSI: `apps/landing` · `apps/bitsi` · `apps/sibiner` · `packages/ui` | Struktur kembar 3 frontend → komponen UI (card, navbar, org-chart, QR scanner), util fetch, dan design tokens dibagikan; konsistensi visual terjaga (mau eksplisit rizz); satu CI untuk semua |
| S2 | Stack sama dengan BitSI: Vite + React + TS strict + Tailwind + shadcn/ui | Reuse penuh `packages/ui`; developer context-switching minim |
| S3 | Perbedaan hanya di konten & routing, tidak di arsitektur | Bukti desain backend scoped-by-community bekerja |
| S4 | Deploy Cloudflare Pages per app | Konsisten ADR B7 |
| S5 | Label semantik di UI memakai istilah literasi ("diskusi", "rak buku", "rangkuman"), meski endpoint tetap generik (`rapats`, `proyeks`, `kelass`) | UX terasa native komunitas buku, tanpa fork backend |

## 5. State Matrix

Identik dengan BitSI (PRD `bitsi-app.md` §5): skeleton per-card, empty ramah, error inline + retry, guard role, tanpa crash global.

## 6. Success Metrics

1. Rak buku tumbuh organik → calon anggota tertarik lewat konten review
2. Presensi diskusi via QR < 10 detik/orang
3. Rangkuman bacaan mudah diakses member kapan saja
4. Pengurus mengelola anggota tanpa sentuh database langsung

## 7. Open Questions

1. Rating buku (bintang): input manual reviewer atau agregasi dari anggota?
   → Rekomendasi: **manual oleh reviewer** (pengurus) dulu.
2. Cover buku: upload file atau cukup link gambar?
   → Rekomendasi: **upload thumbnail** (konsisten M11) + fallback placeholder cover.

---

*PRD terkait:* `bitsi-app.md` (pola dasar) · backend M11–M12 · `struktur-organisasi.md` (Divisi Organisasi)
