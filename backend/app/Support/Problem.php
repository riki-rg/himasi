<?php

namespace App\Support;

use RuntimeException;

/**
 * RFC 7807 Problem Details exception.
 *
 * Di-render oleh handler di bootstrap/app.php menjadi
 * response application/problem+json.
 */
class Problem extends RuntimeException
{
    /**
     * @param  array<string, array<int, string>>|null  $errors
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $slug,
        public string $problemTitle,
        public int $statusCode,
        public ?string $detail = null,
        public ?array $errors = null,
        public array $extra = [],
    ) {
        parent::__construct($detail ?? $problemTitle);
    }

    public static function validation(array $errors, ?string $detail = null): self
    {
        return new self('validation', 'Data tidak valid', 422, $detail ?? 'Beberapa field tidak sesuai.', $errors);
    }

    public static function unauthorized(?string $detail = null): self
    {
        return new self('unauthorized', 'Belum terautentikasi', 401, $detail ?? 'Token tidak valid atau sudah kedaluwarsa.');
    }

    public static function forbidden(?string $detail = null): self
    {
        return new self('forbidden', 'Akses ditolak', 403, $detail ?? 'Kamu tidak punya izin untuk aksi ini.');
    }

    public static function notFound(?string $detail = null): self
    {
        return new self('not-found', 'Tidak ditemukan', 404, $detail ?? 'Resource yang diminta tidak ada.');
    }

    public static function conflict(string $detail): self
    {
        return new self('conflict', 'Konflik state', 409, $detail);
    }

    public static function accountPending(): self
    {
        return new self(
            'account-pending',
            'Akun menunggu persetujuan',
            423,
            'Akunmu masih menunggu persetujuan admin. Coba lagi nanti atau hubungi pengurus.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => rtrim(config('app.url'), '/').'/problems/'.$this->slug,
            'title' => $this->problemTitle,
            'status' => $this->statusCode,
            'detail' => $this->detail,
            'errors' => $this->errors,
            ...$this->extra,
        ], fn ($value) => $value !== null);
    }
}
