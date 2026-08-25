# Struktur Organisasi Standar HIMSI UMKU

> Status: Final v1 · Tanggal: 2026-08-25 · Sumber: definisi langsung dari rizz
> Fungsi dokumen: acuan **DatabaseSeeder** + validasi endpoint `/publik/struktur`
> Berlaku per periode kepengurusan — struktur berulang tiap periode baru, hanya personelnya berganti

---

## 1. Definisi Divisi & Jabatan

| # | Divisi | Jabatan (tingkat) | Mengelola komunitas | Catatan |
|---|--------|-------------------|---------------------|---------|
| 1 | **BPH** — Badan Pengurus Harian | Ketua Umum (utama) · Wakil Ketua Umum (utama) · Sekretaris Umum (utama) · Bendahara Umum (utama) | — | Inti himpunan; tampil paling atas di org chart |
| 2 | **Divisi Pengembangan Diri** | Ketua Divisi (utama) · Sekretaris Divisi (staf) · Anggota Divisi (anggota) | ⚡ **BitSI** | Program unggulan: Web Dev, IoT, Jaringan, Server |
| 3 | **Divisi Organisasi** | Ketua Divisi (utama) · Sekretaris Divisi (staf) · Anggota Divisi (anggota) | 📚 **Sibiner** | Program literasi: bicara nalar & baca buku |
| 4 | **Medkom** — Media Komunikasi dan Publikasi | Ketua Divisi (utama) · Sekretaris Divisi (staf) · Anggota Divisi (anggota) | — | Publikasi & dokumentasi |
| 5 | **Ekowir** — Ekonomi dan Kewirausahaan | Ketua Divisi (utama) · Sekretaris Divisi (staf) · Anggota Divisi (anggota) | — | |

> Semantik kolom `divisis.komunitas_id`: *"divisi inilah yang menjalankan komunitas X"* — bukan kepemilikan eksklusif. Divisi tanpa nilai = bagian inti HIMSI saja.

## 2. Implikasi ke API `/publik/struktur`

| Permintaan client | Hasil yang dikembalikan |
|---|---|
| Tanpa filter (landing page) | Tree lengkap: 5 divisi sesuai urutan tabel di atas |
| `?komunitas=BITSI` (app BitSI) | Divisi Pengembangan Diri + semua jabatan & pengurusnya |
| `?komunitas=SIBINER` (app Sibiner) | Divisi Organisasi + semua jabatan & pengurusnya |

Urutan render org chart: BPH dulu, lalu divisi lain sesuai `urutan`.

## 3. Spesifikasi Seeder (untuk fase implementasi)

```php
// Komunitas (reference data, sekali saja)
['kode' => 'HIMSI',   'nama' => 'Himpunan Mahasiswa Sistem Informasi'],
['kode' => 'BITSI',   'nama' => 'BitSI - Bit Of Sistem Informasi'],
['kode' => 'SIBINER', 'nama' => 'Sibiner - SI Bicara Nalar & Literasi'],

// Per contoh periode "Kepengurusan {tahun}":
$divisi = [
    ['nama' => 'BPH',                              'urutan' => 1, 'komunitas' => null],
    ['nama' => 'Divisi Pengembangan Diri',         'urutan' => 2, 'komunitas' => 'BITSI'],
    ['nama' => 'Divisi Organisasi',                'urutan' => 3, 'komunitas' => 'SIBINER'],
    ['nama' => 'Media Komunikasi dan Publikasi (Medkom)', 'urutan' => 4, 'komunitas' => null],
    ['nama' => 'Ekonomi dan Kewirausahaan (Ekowir)',      'urutan' => 5, 'komunitas' => null],
];

// Jabatan per divisi
'BPH'        => [['Ketua Umum','utama'],['Wakil Ketua Umum','utama'],
                 ['Sekretaris Umum','utama'],['Bendahara Umum','utama']],
'DEFAULT'    => [['Ketua Divisi','utama'],['Sekretaris Divisi','staf'],
                 ['Anggota Divisi','anggota']],
```

Total jabatan standar: **4 (BPH) + 3×4 (divisi lain) = 16 posisi per periode**.

## 4. Catatan Role Akses vs Jabatan

Jabatan di atas adalah **konten org chart** (data tampilan). Role hak akses API tetap mengikuti PRD §3 dan dipetakan otomatis dari penugasan:

| Penugasan pada… | Role efektif yang diberikan sistem |
|---|---|
| Ketua Umum / Wakil | admin-level komunitas HIMSI |
| Ketua Divisi (mengelola komunitas) | pengurus komunitas terkait (approve pendaftar, QR presenter, CRUD karya/kelas) |
| Bendahara Umum | akses modul keuangan |
| Sekretaris Umum | akses modul surat |
| Lainnya | anggota |

> Dengan pemetaan ini, admin tidak perlu set role manual tiap pergantian pengurus — cukup input penugasan baru, role ikut berubah.

---

*Terintegrasi dengan:* ERD (`docs/design/erd.md` §1) · PRD backend M2 · US-01 (master data)
