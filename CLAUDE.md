# CLAUDE.md — Backend API HIMSI UMKU (hima 2)

> Context untuk AI-assisted development.
> **`docs/api/openapi.yaml` = SUMBER KEBENARAN ENDPOINT. ADR terkunci — jangan ubah tanpa izin owner (rizz).**
> Dokumen desain: `docs/prd/*` · `docs/design/erd.md` · `docs/design/wireframes/*`

## Lokasi

```
/home/rizz/Documents/projects/hima 2/     ← ROOT (hanya docs + README saat ini)
├── docs/                                 ← PRD, ERD, openapi.yaml, wireframes
└── backend/                              ← Laravel 13 · Breeze API mode · Sanctum
```

⚠️ `/home/rizz/Documents/projects/hima/` dan `/home/rizz/himsi` = project lama. **Jangan disentuh.**

## Perintah (dari `backend/`)

```bash
php artisan serve --port=8010              # API → http://localhost:8010/api/v1
php artisan test                           # PHPUnit (sqlite :memory:)
vendor/bin/pint --dirty                    # format PSR-12 hanya file berubah
php artisan migrate:fresh --seed           # reset DB + seed struktur standar
```

## Konvensi

- Prefix global **api/v1** diset via `apiPrefix` di `bootstrap/app.php`.
- Error **RFC 7807 problem+json**: lempar `App\Support\Problem` (`Problem::validation()`, `::unauthorized()`, `::accountPending()` dst.) — renderer ada di `bootstrap/app.php`. Extension `errors{}` otomatis untuk 422 dari `ValidationException`.
- Model: atribut **`#[Fillable]` / `#[Hidden]`** + method `casts()` (bukan property `$fillable`).
- Money selalu `decimal:2` cast → string di JSON. Jangan float.
- Upload ≤5MB foto / ≤10MB PDF via `Storage::disk('public')`.
- Role & permission dipetakan OTOMATIS dari penugasan aktif: `App\Services\RoleResolver` + Gates `admin-pusat`, `bendahara`, `sekretaris`, `pengurus-komunitas:{kode}`. Ketua/Wakil Umum = admin pusat.
- Bahasa: field/kode EN, pesan user-facing Bahasa Indonesia.

## Database

- Dev: **sqlite** (`database/database.sqlite`). Deploy VM nanti: ganti `DB_CONNECTION=mysql|pgsql` di `.env` — migration ditulis engine-agnostic.
- Test: sqlite `:memory:` (sudah diset `phpunit.xml`).
- Skema final = `docs/design/erd.md`. Amendment yang sudah disetujui owner: kolom `users.status` enum(pending, aktif) — approval akun (ADR D6).

## Quality gates sebelum claim "selesai"

1. `vendor/bin/pint --test` bersih
2. `php artisan test` pass
3. Manual verify endpoint sesuai contoh openapi.yaml (`curl`, cek content-type problem+json pada error path)
