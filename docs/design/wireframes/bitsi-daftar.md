# Wireframe — Halaman `/daftar` App BitSI

> Route: `/daftar` · Render: Static (form SPA) · Status: Draft v1
> Referensi visual: **Engineering Blueprint** (`~/Documents/projects/bitsi/`) — paper `#fafbfe` × cobalt × sky · IBM Plex Sans/Mono · blueprint grid · section bernomor · animasi rise berjenjang
> Fungsi: satu-satunya pintu masuk member baru (akun + auto-apply komunitas BITSI)

---

## 1. Layout ASCII

### Desktop (≥1024px)

```
┌──────────────────────────────────────────────────────────────────┐
│ ▣ BitSI   Pengenalan  ·  Bidang  ·  Karya        [Masuk]         │ ← navbar tipis,
│                                                                  │   backdrop blur
├──────────────────────────────────────────────────────────────────┤
│ ░░░░░░░░░░░░░░░ blueprint-grid bg ░░░░░░░░░░░░░░░░░░░░░░░░░░░░ │
│                                                                  │
│  ┌ KIRI · FORM (55%) ──────────┐  ┌ KANAN · VALUE (45%) ─────┐ │
│  │ ◉ pulse-dot                 │  │                           │ │
│  │ GABUNG BITSI      (mono,    │  │  Kenapa gabung?           │ │
│  │ tracking-widest)            │  │                           │ │
│  │                             │  │  ┌─ 01 ─────────────┐    │ │
│  │ Satu form. Tiga langkah.    │  │  │ 🌐 Belajar 4     │    │ │
│  │                             │  │  │ bidang tech      │    │ │
│  │ Langkah 1 dari 1            │  │  ├─ 02 ─────────────┤    │ │
│  │ ●────────○────────○         │  │  │ 🛠 Praktik       │    │ │
│  │ Daftar  Review  Aktif       │  │  │ langsung proyek  │    │ │
│  │                             │  │  ├─ 03 ─────────────┤    │ │
│  │ NIM*                        │  │  │ 👥 Komunitas     │    │ │
│  │ ┌─────────────────────┐    │  │  │ ngoprek bareng   │    │ │
│  │ │ 2101050001          │    │  │  └──────────────────┘    │ │
│  │ └─────────────────────┘    │  │                           │ │
│  │ Nama Lengkap*              │  │  ── alur setelah ini ──   │ │
│  │ ┌─────────────────────┐    │  │  ⏳ Ketua review ≤3 hari  │ │
│  │ │                     │    │  │  ✅ Notif disetujui       │ │
│  │ └─────────────────────┘    │  │  🚀 Login & mulai         │ │
│  │                            │  │                           │ │
│  │ Email UMKU*      Angkatan* │  │  ┌──────────────────┐     │ │
│  │ ┌──────────────┐┌───────┐  │  │  │ foto kegiatan    │     │ │
│  │ └──────────────┘└───────┘  │  │  │ (polaroid tilt)  │     │ │
│  │ Password*        No. HP    │  │  └──────────────────┘     │ │
│  │ ┌──────────────┐┌───────┐  │  │  👁👁 toggle show pw       │ │
│  │ └──────────────┘└───────┘  │  │                           │ │
│  │ Prodi (dropdown SI)        │  │  Sudah punya akun?        │ │
│  │ ┌─────────────────────┐    │  │  [Masuk →] link cobalt    │ │
│  │ │ Sistem Informasi  ▾ │    │  │                           │ │
│  │ └─────────────────────┘    │  │                           │ │
│  │ Link Portofolio  Link IG   │  │                           │ │
│  │ ┌──────────────┐┌───────┐  │  │                           │ │
│  │ │ opsional     ││opsilon│  │  │                           │ │
│  │ └──────────────┘└───────┘  │  │                           │ │
│  │                            │  │                           │ │
│  │ ☑ Saya anggota HIMSI aktif │  │                           │ │
│  │   & setuju ketentuan       │  │                           │ │
│  │                            │  │                           │ │
│  │ [ DAFTAR SEKARANG → ]      │  │                           │ │
│  │   (cobalt solid, full-w)   │  │                           │ │
│  └────────────────────────────┘  └───────────────────────────┘ │
│                                                                  │
│  footer mini: © BitSI · HIMSI UMKU                    v1.0      │
└──────────────────────────────────────────────────────────────────┘
```

### Mobile (<640px)

```
┌────────────────────┐
│ ▣ BitSI      ☰     │
├────────────────────┤
│ GABUNG BITSI       │
│ ●──○──○            │
├────────────────────┤
│ NIM*               │
│ ┌────────────────┐ │
│ └────────────────┘ │
│ Nama Lengkap*      │
│ ...field stack...  │
│ (urutan sama,      │
│  1 kolom)          │
│                    │
│ ☑ ketentuan        │
│ [DAFTAR →]         │
├────────────────────┤
│ Sudah punya akun?  │
│ Masuk →            │
│ ── collapse panel  │
│ "kenapa gabung"    │
│ jadi accordion     │
│ di bawah form      │
└────────────────────┘
```

## 2. Component Tree

```
<RootLayout>                          (font Plex, blueprint theme)
└── <DaftarPage>
    ├── <Navbar variant="minimal">    logo + [Masuk] saja
    ├── <StepperIndicator>            Daftar → Review → Aktif (step 1 aktif)
    ├── <RegisterForm>                client-side validation (zod)
    │   ├── <Field nim*>              pattern NIM, cek duplikat via API error
    │   ├── <Field nama*>
    │   ├── <Field email*>            format email
    │   ├── <Field angkatan*>         select tahun masuk
    │   ├── <Field password*>         min 8 + strength hint + eye toggle
    │   ├── <Field noHp>              opsional
    │   ├── <SelectProdi>             default "Sistem Informasi"
    │   ├── <Field portofolio>        url opsional
    │   ├── <Field instagram>         @username opsional
    │   ├── <CheckboxSyarat*>         wajib centang sebelum submit
    │   └── <SubmitButton>            loading spinner saat POST
    ├── <ValuePanel>                  kenapa gabung (3 poin) + polaroid foto
    │   └── <AlurSetelahDaftar>       timeline 3 langkah
    └── <FooterMini>
```

## 3. State Matrix

| Kondisi | Perilaku |
|---------|----------|
| Idle | Tombol disabled sampai field wajib valid + checkbox tercentang |
| Submitting | Tombol → spinner "Memproses…", semua input locked |
| Sukses | **Layar ganti** (bukan alert): ikon ⏳ besar animasi pulse + "Pendaftaran terkirim!" + penjelasan menunggu persetujuan ketua + tombol "Kembali ke Beranda". Stepper pindah ke step 2 (Review) |
| Validasi gagal (422 RFC 7807) | Error inline merah di bawah field terkait — mapping dari `errors{}` problem+json; scroll otomatis ke field error pertama |
| NIM duplikat | Field NIM shake + pesan "NIM sudah terdaftar — coba [Masuk]?" |
| Network error | Banner cobalt-muted atas form: "Koneksi bermasalah" + tombol Coba Lagi; data form TIDAK hilang |
| JS gagal total | `<noscript>` fallback: instruksi daftar manual via kontak pengurus |

## 4. Catatan UX Detail

- **Satu layar tanpa multi-step wizard** — 8 field masih nyaman satu halaman; stepper 3 titik hanya komunikasi proses, bukan wizard
- Password: `type` toggle mata + hint "min. 8 karakter" real-time
- Auto-format: NIM digits-only; No HP auto `08…`; Instagram auto-prefix `@`
- Simpan draft form ke localStorage (auto-recover jika browser tertutup)
- Setelah sukses: simpan flag `pending_registration` — kalau user buka `/daftar` lagi, tampilkan status "Kamu sudah mendaftar, menunggu review" bukan form kosong
- Aksesibilitas: label eksplisit semua field, focus ring cobalt kontras, error pakai `aria-describedby`
- Bahasa form santai-formal khas mahasiswa ("Gabung", "Ngoprek"), label tetap jelas

## 5. Responsive Notes

| Breakpoint | Perilaku |
|-----------|----------|
| `<640px` | 1 kolom; ValuePanel collapse jadi accordion di bawah form; navbar hamburger |
| `640–1023px` | Form max-w-md center; ValuePanel di atas form versi ringkas horizontal chips |
| `≥1024px` | Grid 55/45 seperti wireframe; sticky ValuePanel saat form panjang |

---

✅ **Amendment diterapkan**: `POST /auth/register` di `docs/api/openapi.yaml` sudah memiliki properti opsional `komunitas` (kode) — auto-apply BitSI terjadi dalam satu transaksi (akun + langsung masuk antrean review ketua).

*Terintegrasi:* PRD `bitsi-app.md` §3 (route `/daftar`) · backend US-02 & US-06 · openapi.yaml `POST /auth/register` (dengan `komunitas`) + `POST /keanggotaan`
