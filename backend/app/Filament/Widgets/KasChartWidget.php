<?php

namespace App\Filament\Widgets;

use App\Models\Periode;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class KasChartWidget extends ChartWidget
{
    protected ?string $heading = 'Keuangan per bulan (periode aktif)';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $periode = Periode::aktif();

        $rows = DB::table('kas')
            ->when($periode, fn ($q) => $q->where('periode_id', $periode?->id))
            ->selectRaw("strftime('%m', tanggal) as bulan")
            ->selectRaw("SUM(CASE WHEN tipe = 'pemasukan' THEN nominal ELSE 0 END) as masuk")
            ->selectRaw("SUM(CASE WHEN tipe = 'pengeluaran' THEN nominal ELSE 0 END) as keluar")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $labelBulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $labels = collect(range(1, 12))->map(fn ($b) => $labelBulan[$b]);
        $masuk = [];
        $keluar = [];

        foreach (range(1, 12) as $b) {
            $baris = $rows->firstWhere('bulan', str_pad((string) $b, 2, '0', STR_PAD_LEFT));
            $masuk[] = (float) ($baris->masuk ?? 0);
            $keluar[] = (float) ($baris->keluar ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $masuk,
                    'backgroundColor' => '#22c55e',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $keluar,
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $labels->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
