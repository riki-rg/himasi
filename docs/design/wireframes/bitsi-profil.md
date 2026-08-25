# Wireframe — Profil `/app/profil` App BitSI

> Status: Draft v1 · Guard: member · Sumber: M7 (data anggota + portofolio & IG)

---

## 1. Layout ASCII

```
┌──────────────────────────────────────────────┐
│ ← Dashboard          PROFIL SAYA             │
├──────────────────────────────────────────────┤
│        ┌─────────┐                           │
│        │ [FOTO]  │  ← tap = ganti foto       │
│        └─────────┘    (upload/crop sederhana)│
│         Rizky Maulana                        │
│         ⚡ Anggota BitSI · aktif              │
│         🎓 2022 · SI                          │
│                                              │
│ DATA DIRI                                    │
│ NIM          2210500123          (readonly)  │
│ Nama*        [ Rizky Maulana            ]    │
│ Email*       [ rizky@…                  ]    │
│ No HP        [ 0812…                   ]    │
│ Prodi        [ Sistem Informasi      ▾ ]    │
│ Alamat       [ …                       ]    │
│                                              │
│ TAUTAN                                       │
│ Portofolio [ https://…                 ] 🔗  │
│ Instagram  [ @rizky.dev                ]     │
│                                              │
│ KEAMANAN                                     │
│ [ Ganti password → ]                         │
│                                              │
│ [ SIMPAN PERUBAHAN ]     (muncul jika dirty) │
└──────────────────────────────────────────────┘
```

## 2. Component Tree

```
<ProfilPage>
├── <AvatarUpload>                preview + remove; kompres client-side
├── <ProfileForm>
│   ├── readonly: NIM, status keanggotaan, komunitas
│   ├── editable: nama, email, no_hp, prodi, alamat
│   ├── <LinkFields>              portofolio & instagram dengan validasi URL/@handle
├── <GantiPasswordDialog>         password lama + baru + konfirmasi
└── <SaveBar>                     sticky saat ada perubahan (dirty state)
```

## 3. State Matrix

| Kondisi | Perilaku |
|---------|----------|
| Loading | skeleton form |
| Simpan sukses | toast "Profil tersimpan" + bar hilang |
| Validasi gagal 422 | error per-field inline |
| Foto >5MB | tolak sebelum upload + pesan batas |
| Ganti password sukses | toast; sesi tetap |

## 4. UX Notes

- NIM & status **readonly** — data identitas resmi hanya lewat admin/pengurus (integritas data)
- SaveBar muncul hanya ketika ada perubahan (dirty) — cegah simpan tak sengaja & hilang perubahan tak disadari
- Peringatan navigasi saat dirty ("Perubahan belum disimpan")
- Link portofolio/IG langsung tampil di org chart publik — ingatkan user lewat caption kecil

## 5. Responsive

Form satu kolom mobile; dua kolom desktop (identitas kiri, tautan kanan).
