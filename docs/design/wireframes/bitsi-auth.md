# Wireframe — Login `/login` App BitSI

> Status: Draft v1 · Visual: Engineering Blueprint · Guard entry point semua area member/pengurus

---

## 1. Layout ASCII

```
┌──────────────────────────────────────────────┐
│ ░ blueprint-grid ░░░░░░░░░░░░░░░░░░░░░░░░░░ │
│                                              │
│         ▣ logo BitSI                         │
│         SELAMAT DATANG KEMBALI               │
│         (mono tracking-widest)               │
│                                              │
│      ┌──────────────────────────┐            │
│      │ Email                    │            │
│      │ ┌──────────────────────┐ │            │
│      │ │                      │ │            │
│      │ └──────────────────────┘ │            │
│      │ Password                 │            │
│      │ ┌──────────────────┬───┐ │            │
│      │ │                  │ 👁 │ │            │
│      │ └──────────────────┴───┘ │            │
│      │ [ L MASUK → ]            │            │
│      └──────────────────────────┘            │
│                                              │
│      Belum punya akun? [Daftar dulu]         │
│      ← kembali ke beranda                    │
│                                              │
│  ── kondisi tambahan (state matrix) ──       │
│  ⏳ akun pending:                            │
│    "Akunmu masih menunggu persetujuan        │
│     ketua. Biasanya ≤ 3 hari.                │
│     Kontak pengurus: WA link"                │
│  🚫 ditolak:                                 │
│    "Pendaftaran belum disetujui." + alasan   │
└──────────────────────────────────────────────┘
```

## 2. Component Tree

```
<LoginPage>
├── <LoginForm>
│   ├── <FieldEmail*>
│   ├── <FieldPassword*>          eye toggle
│   └── <SubmitButton>            loading spinner
├── <PendingScreen>               HTTP 423 → layar status approval
├── <RejectedScreen>              alasan penolakan jika ada
└── <FooterLinks>                 daftar · beranda
```

## 3. State Matrix

| Kondisi | Perilaku |
|---------|----------|
| Submitting | tombol spinner; input locked |
| Sukses | redirect `/app` (atau `?redirect=` asal) |
| 401 salah kredensial | error inline di bawah form: "Email atau password salah" |
| 423 pending | ganti seluruh form → PendingScreen (jangan cuma toast!) |
| Akun ditolak | RejectedScreen + alasan + kontak pengurus |
| Network error | banner retry; form utuh |
| Sudah login buka /login | auto-redirect `/app` |

## 4. UX Notes

- Pending/ditolak = **layar penuh**, bukan pesan error kecil — konteks emosional berbeda (bukan kesalahan user)
- Simpan email terakhir di localStorage untuk pre-fill
- Rate limit client: disable submit 3 detik setelah 3x gagal (backend juga rate-limit)

## 5. Responsive

Mobile-first penuh — kartu max-w-sm center; tidak ada sidebar/tab bar.
