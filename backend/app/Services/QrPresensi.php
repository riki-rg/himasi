<?php

namespace App\Services;

use App\Models\Rapat;
use Illuminate\Support\Carbon;

/**
 * Presensi QR berotasi 60 detik (ADR D1).
 *
 * Token = "{rapatId}|{window}|{hmac}" dengan
 * hmac = HMAC-SHA256("{rapatId}|{window}", qr_secret).
 * Server tidak pernah menulis DB saat rotasi — validasi murni komputasi.
 */
class QrPresensi
{
    public const ROTASI_DETIK = 60;

    private const BUKA_SEBELUM_MENIT = 15;

    private const TUTUP_SETELAH_MENIT = 120;

    public function window(): int
    {
        return intdiv(now()->getTimestamp(), self::ROTASI_DETIK);
    }

    public function sisaDetik(): int
    {
        return self::ROTASI_DETIK - (now()->getTimestamp() % self::ROTASI_DETIK);
    }

    /**
     * @return array{payload: string, expires_in: int}
     */
    public function buatPayload(Rapat $rapat): array
    {
        $w = $this->window();

        return [
            'payload' => $rapat->id.'|'.$w.'|'.$this->mac($rapat, $w),
            'expires_in' => max(1, $this->sisaDetik()),
        ];
    }

    /**
     * @return 'valid'|'expired'|'invalid'
     */
    public function verifikasi(Rapat $rapat, string $token): string
    {
        $bagian = explode('|', $token);

        if (count($bagian) !== 3 || (int) $bagian[0] !== (int) $rapat->id || ! ctype_digit($bagian[1])) {
            return 'invalid';
        }

        [, $w, $mac] = $bagian;

        if (! hash_equals($this->mac($rapat, (int) $w), $mac)) {
            return 'invalid';
        }

        return (int) $w === $this->window() ? 'valid' : 'expired';
    }

    /** Jendela absensi: hari-H, mulai H-15m s.d. selesai+120m. */
    public function jendelaAktif(Rapat $rapat): bool
    {
        if (! $rapat->tanggal->isSameDay(now())) {
            return false;
        }

        $mulai = Carbon::parse($rapat->tanggal->toDateString().' '.$rapat->jam_mulai)
            ->subMinutes(self::BUKA_SEBELUM_MENIT);

        $selesai = Carbon::parse($rapat->tanggal->toDateString().' '.($rapat->jam_selesai ?? $rapat->jam_mulai))
            ->addMinutes($rapat->jam_selesai !== null ? self::TUTUP_SETELAH_MENIT : 180);

        return now()->betweenIncluded($mulai, $selesai);
    }

    private function mac(Rapat $rapat, int $window): string
    {
        return hash_hmac('sha256', $rapat->id.'|'.$window, $rapat->qr_secret);
    }
}
