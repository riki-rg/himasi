# Index Wireframe — Ekosistem HIMSI UMKU

> Terakhir diperbarui: 2026-08-25 · Total: 16 dokumen wireframe
> Identitas visual: **Engineering Blueprint** (landing + BitSI — putih × cobalt × IBM Plex) · **Warm Library** (Sibiner — krem × hijau hutan × Cormorant/Libre Baskerville)

---

## 🏠 Landing Page HIMSI (Next.js)

| Dokumen | Route | Cakupan |
|---------|-------|---------|
| `home.md` | `/` | One-pager utama: hero, pengumuman, tentang, komunitas, struktur preview, berita, agenda, galeri |
| `landing-artikel.md` | `/artikel` + `/artikel/[slug]` | List dengan filter/search + halaman baca (prose, share, related) |
| `landing-agenda-galeri.md` | `/agenda` + `/galeri` | Tab mendatang/lampau + index album |
| `galeri-album.md` | `/galeri/[album]` | Masonry grid + lightbox deep-linkable |
| `landing-struktur.md` | `/struktur` | Org chart lengkap per periode, BPH hierarkis |

## ⚡ App BitSI (React SPA)

| Dokumen | Route | Cakupan |
|---------|-------|---------|
| `bitsi-daftar.md` | `/daftar` | Registrasi member + auto-apply; state pending screen |
| `bitsi-auth.md` | `/login` | Login + layar pending/ditolak |
| `bitsi-dashboard.md` | `/app` | Hub member: rapat hero card, kelas, pengumuman, stat |
| `bitsi-rapat-detail.md` | `/app/rapat/:id` | Siklus rapat + scanner absensi + fallback manual |
| `bitsi-presenter-qr.md` | `/app/pengurus/presenter` | Layar fullscreen QR rotasi 60s + live counter |
| `bitsi-kelas.md` | `/app/kelas(/:id)` | Katalog kelas per bidang + unduh materi |
| `bitsi-profil.md` | `/app/profil` | Edit profil + tautan portofolio/IG |
| `bitsi-pengurus-pendaftar.md` | `/app/pengurus/pendaftar` | Approve/tolak pendaftar + undo toast |
| `bitsi-pengurus-kelola.md` | `/app/pengurus/karya`, `/kelas` | CRUD karya & kelas-materi (pola tabel standar) |

## 📚 App Sibiner (React SPA)

| Dokumen | Route | Cakupan |
|---------|-------|---------|
| `sibiner-home.md` | `/` | Rak buku centerpiece + identitas Warm Library |
| `sibiner-member.md` | `/app/diskusi`, `/app/bacaan` | Mirror pola BitSI + perbedaan konten literasi |

---

## Konvensi Lintas Wireframe

1. Setiap section data-driven punya **state matrix**: loading / empty / error / success
2. Error API RFC 7807 dipetakan ke reaksi UI spesifik (bukan toast generik)
3. Touch target ≥44px, kontras ≥4.5:1, focus ring terlihat (checklist skill ui-ux-pro-max)
4. Mobile-first; breakpoint 640/768/1024
5. Komponen struktural dibagikan via monorepo `packages/ui`; yang dibedakan antar-app hanya design tokens
6. Anti-blank: API down tidak boleh membuat halaman kosong total

*Pasangan dokumen:* PRD (`docs/prd/*`) · ERD · API spec (`docs/api/openapi.yaml`)
