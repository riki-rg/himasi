<?php

namespace App\Services;

use App\Models\Member;
use App\Support\Problem;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Import/export anggota xlsx & csv via openspout (streaming, tanpa ext-gd).
 * Format kolom: nim, nama, prodi, angkatan, email, no_hp, status.
 */
class MemberImportExporter
{
    private const KOLOM = ['nim', 'nama', 'prodi', 'angkatan', 'email', 'no_hp', 'status'];

    /**
     * @return array{0: int, 1: array<int, array{baris: int, alasan: string}>}
     */
    public function importAnggota(string $path): array
    {
        $rows = $this->bacaBaris($path);

        $berhasil = 0;
        $gagal = [];
        $nimDalamFile = [];

        foreach ($rows as $i => $row) {
            $barisKe = $i + 1;
            $data = array_combine(self::KOLOM, array_pad(array_slice($row, 0, count(self::KOLOM)), count(self::KOLOM), null));
            $data = array_map(fn ($v) => trim((string) $v), $data);

            if (implode('', $data) === '') {
                continue;
            }

            $alasan = $this->validasiBaris($data, $nimDalamFile);

            if ($alasan !== null) {
                $gagal[] = ['baris' => $barisKe + 1, 'alasan' => $alasan];

                continue;
            }

            $nimDalamFile[] = $data['nim'];

            Member::query()->create([
                'nim' => $data['nim'],
                'nama' => $data['nama'],
                'angkatan' => $data['angkatan'],
                'email' => $data['email'] !== '' ? $data['email'] : null,
                'no_hp' => $data['no_hp'] !== '' ? $data['no_hp'] : null,
                'status' => in_array($data['status'], ['aktif', 'nonaktif', 'alumni'], true)
                    ? $data['status']
                    : 'aktif',
            ]);

            $berhasil++;
        }

        return [$berhasil, $gagal];
    }

    public function exportAnggota(Builder $query, string $format): StreamedResponse
    {
        $filename = 'anggota-himsi-'.now()->format('Ymd-His').".$format";

        return response()->streamDownload(function () use ($query, $format) {
            $writer = $format === 'csv'
                ? new CsvWriter
                : new XlsxWriter;

            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues(['NIM', 'Nama', 'Prodi', 'Angkatan', 'Email', 'No HP', 'Status']));

            $query->chunk(500, function ($members) use ($writer) {
                foreach ($members as $m) {
                    $writer->addRow(Row::fromValues([
                        $m->nim, $m->nama, $m->prodi ?? '', $m->angkatan,
                        $m->email ?? '', $m->no_hp ?? '', $m->status,
                    ]));
                }
            });

            $writer->close();
        }, $filename, [
            'Content-Type' => $format === 'csv'
                ? 'text/csv; charset=UTF-8'
                : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @param  array<string, string>  $data
     * @param  array<int, string>  $nimDalamFile
     */
    private function validasiBaris(array $data, array $nimDalamFile): ?string
    {
        if ($data['nim'] === '') {
            return 'NIM kosong.';
        }

        if (! preg_match('/^\d{1,20}$/', $data['nim'])) {
            return "NIM '{$data['nim']}' tidak valid.";
        }

        if ($data['nama'] === '') {
            return 'Nama kosong.';
        }

        if (! preg_match('/^\d{4}$/', $data['angkatan'])) {
            return "Angkatan '{$data['angkatan']}' harus 4 digit.";
        }

        if ($data['email'] !== '' && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return "Email '{$data['email']}' tidak valid.";
        }

        if (in_array($data['nim'], $nimDalamFile, true)) {
            return "NIM '{$data['nim']}' duplikat dalam file.";
        }

        if (Member::query()->where('nim', $data['nim'])->exists()) {
            return "NIM '{$data['nim']}' sudah terdaftar.";
        }

        return null;
    }

    /**
     * Baris data tanpa header — baris pertama dianggap header.
     *
     * @return list<list<string>>
     */
    private function bacaBaris(string $path): array
    {
        $reader = $this->deteksiTipe($path)
            ? new CsvReader
            : new XlsxReader;

        try {
            $reader->open($path);
        } catch (\Throwable) {
            throw Problem::validation(
                ['file' => ['File tidak bisa dibaca sebagai csv atau xlsx.']]
            );
        }

        $rows = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $index => $row) {
                if ($index === 1) {
                    continue;
                }

                $rows[] = array_map(
                    fn ($value) => trim((string) ($value ?? '')),
                    $row->toArray()
                );
            }

            break;
        }

        $reader->close();

        return $rows;
    }

    private function deteksiTipe(string $path): bool
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'csv';
    }
}
