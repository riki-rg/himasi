<?php

namespace App\Filament\Widgets;

use App\Models\IuranMember;
use App\Models\Kas;
use App\Models\Member;
use App\Models\Periode;
use App\Models\Surat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsHimsiWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $polling = '30s';

    protected function getStats(): array
    {
        $periode = Periode::aktif();

        $saldo = (float) Kas::query()
            ->when($periode, fn ($q) => $q->where('periode_id', $periode?->id))
            ->selectRaw("COALESCE(SUM(CASE WHEN tipe = 'pemasukan' THEN nominal ELSE -nominal END), 0) as saldo")
            ->value('saldo');

        return [
            Stat::make('Anggota aktif', Member::query()->where('status', 'aktif')->count())
                ->description('seluruh himpunan')
                ->color('success'),
            Stat::make('Saldo kas', 'Rp\u{00A0}'.number_format($saldo, 0, ',', '.'))
                ->description($periode?->nama ?? '-'),
            Stat::make('Surat menunggu review', Surat::query()->where('jenis', 'keluar')->where('status', 'review')->count()),
            Stat::make('Tagihan belum lunas', IuranMember::query()->where('status', 'belum')->count())
                ->color('warning'),
        ];
    }
}
