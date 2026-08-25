<?php

namespace App\Services;

use App\Models\SuratTemplate;
use Illuminate\Support\Carbon;

/**
 * Penomoran surat keluar otomatis dari template DB (ADR D4).
 * Format placeholder: {urut} · {romawi} · {tahun}.
 */
class PenomoranSurat
{
    private const ROMAWI = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    /**
     * Naikkan counter template secara atomik dan render nomor baru.
     */
    public function nomorBerikutnya(SuratTemplate $template, string $tanggalSurat): string
    {
        $template->counter = $template->counter + 1;
        $template->save();

        return $this->render($template->format, $template->counter, $tanggalSurat);
    }

    public function render(string $format, int $urut, string $tanggal): string
    {
        $tgl = Carbon::parse($tanggal);

        return str_replace(
            ['{urut}', '{romawi}', '{tahun}'],
            [str_pad((string) $urut, 3, '0', STR_PAD_LEFT), self::ROMAWI[$tgl->month], $tgl->year],
            $format
        );
    }
}
