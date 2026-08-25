# PRD — Landing Page HIMSI UMKU (Next.js)

> Status: Draft v1 · Tanggal: 2026-08-25 · Pemilik: rizz
> Konsumen endpoint publik dari Backend API (`docs/api/openapi.yaml`, prefix `/publik/*`)
> Wireframe: `docs/design/wireframes/home.md`

---

## 1. Latar Belakang

HIMSI UMKU belum punya rumah digital resmi. Informasi himpunan tersebar di Instagram dan WA group. Landing page = wajah resmi yang menampilkan profil, struktur, kegiatan, berita, dan gerbang masuk ke aplikasi komunitas BitSI & Sibiner.

## 2. Goals / Non-goals

### Goals
1. Website organisasi yang **modern, cepat, dan SEO-friendly** — kesan pertama mahasiswa baru & pihak kampus.
2. Menampilkan konten dinamis dari backend: artikel, event, galeri, pengumuman, struktur organisasi.
3. Jadi **gerbang** ke aplikasi komunitas (BitSI, Sibiner).
4. Performa: Core Web Vitals hijau (LCP < 2.5s), skor Lighthouse ≥ 90 semua kategori.

### Non-goals
- Fitur login/member di landing page (itu tugas aplikasi komunitas)
- CMS sendiri (konten dikelola lewat backend Laravel)
- Multi-bahasa (Indonesia saja)

## 3. Halaman (arsitektur hybrid)

| Route | Tipe render | Konten |
|-------|-------------|--------|
| `/` | Static + ISR | Hero · pengumuman penting · tentang & visi-misi · statistik · komunitas showcase · struktur (preview) · artikel terbaru · agenda mendatang · galeri pilihan |
| `/artikel` | ISR | List semua artikel + filter kategori + pagination |
| `/artikel/[slug]` | Dynamic/ISR | Detail artikel (SEO meta + OG image otomatis) |
| `/agenda` | ISR | List event, tab "mendatang / lampau" |
| `/galeri` | ISR | Grid album |
| `/galeri/[album]` | Dynamic | Lightbox foto album |
| `/struktur` | Static-ish | Org chart lengkap 3 komunitas (tab per komunitas) |
| `404 / error` | Static | Custom, ramah |

> Konten statis (visi-misi, tentang, kontak) di-hardcode dalam konstanta — bukan API — karena jarang berubah; ubahnya cukup edit satu file.

## 4. Keputusan Teknis (ADR ringkas)

| # | Keputusan | Alasan |
|---|-----------|--------|
| L1 | Next.js App Router + TypeScript strict + Tailwind + shadcn/ui | Default config rizz; type-safe sampai ke response API |
| L1b | Bagian dari **monorepo pnpm workspace** (`apps/landing`) bersama bitsi & sibiner (lihat PRD sibiner ADR S1) | Share komponen UI & design tokens, konsistensi visual |
| L2 | Data fetching via **ISR (`revalidate: 60`)** untuk konten publik | Cepat (cache CDN), tetap fresh ≤60s tanpa server sendiri |
| L3 | `next/image` untuk semua media + lazy loading | Skor performa; gambar dioptimasi otomatis |
| L4 | Metadata API + sitemap.xml + robots.txt + OG dynamic | SEO organisasi; link IG/WA menampilkan preview bagus |
| L5 | Error/loading/empty state wajib per section data | Lihat wireframe state matrix |
| L6 | Deploy: Vercel (config deploy.next) | Zero-config Next.js, domain nanti menyusul |
| L7 | Fallback konten statis saat API down | Section tetap render skeleton + pesan ramah, site tidak blank |

## 5. Success Metrics

1. Lighthouse ≥ 90 (Performance, SEO, Accessibility, Best Practices)
2. Pengunjung bisa temukan info kegiatan terbaru tanpa tanya pengurus
3. Link share IG/WA menampilkan preview OG yang rapi
4. Konten update oleh pengurus muncul di situs ≤ 60 detik tanpa redeploy

## 6. Keputusan Open Questions

| # | Pertanyaan | Keputusan |
|---|-----------|-----------|
| OQ-1 | Domain final | Beli domain sendiri **saat mendekati produksi** (rekomendasi `.web.id`). Struktur: `himsiumku.[domain]` (landing) · `bitsi.` · `sibiner.` · `api.` sebagai subdomain. Selama dev pakai `*.vercel.app` / `*.netlify.app` gratis. Semua URL via env (`NEXT_PUBLIC_API_URL`, dst) agar swap domain tanpa ubah kode |
| OQ-2 | Asset branding | Development memakai **placeholder** (logo/foto generik); asset asli menyusul dari pengurus himpunan |
| OQ-3 | CTA pendaftaran | **Tidak ada form/CTA registrasi di landing page.** Registrasi member adalah fitur aplikasi **BitSI** (dibangun nanti). Tombol "Gabung Komunitas" di landing page hanya mengarah ke URL app BitSI (`NEXT_PUBLIC_BITSI_URL`) — sementara kosong bisa fallback ke Instagram resmi |
