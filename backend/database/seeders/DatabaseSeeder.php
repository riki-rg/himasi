<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\KasKategori;
use App\Models\Komunitas;
use App\Models\Periode;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Struktur standar HIMSI UMKU per docs/design/struktur-organisasi.md.
     * 3 komunitas (reference data) → 1 periode aktif → 5 divisi → 16 jabatan.
     */
    public function run(): void
    {
        $komunitas = [
            ['kode' => 'HIMSI', 'nama' => 'Himpunan Mahasiswa Sistem Informasi'],
            ['kode' => 'BITSI', 'nama' => 'BitSI - Bit Of Sistem Informasi'],
            ['kode' => 'SIBINER', 'nama' => 'Sibiner - SI Bicara Nalar & Literasi'],
        ];

        foreach ($komunitas as $data) {
            Komunitas::query()->create($data);
        }

        foreach ([
            ['nama' => 'Iuran', 'tipe_default' => 'pemasukan'],
            ['nama' => 'Sponsor', 'tipe_default' => 'pemasukan'],
            ['nama' => 'Donasi', 'tipe_default' => 'pemasukan'],
            ['nama' => 'Konsumsi', 'tipe_default' => 'pengeluaran'],
            ['nama' => 'Transportasi', 'tipe_default' => 'pengeluaran'],
            ['nama' => 'Perlengkapan', 'tipe_default' => 'pengeluaran'],
        ] as $kategori) {
            KasKategori::query()->create($kategori);
        }

        $tahun = now()->year;
        $periode = Periode::query()->create([
            'nama' => "Kepengurusan {$tahun}",
            'tanggal_mulai' => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'status' => 'aktif',
        ]);

        $divisi = [
            ['nama' => 'BPH', 'urutan' => 1, 'komunitas' => null],
            ['nama' => 'Divisi Pengembangan Diri', 'urutan' => 2, 'komunitas' => 'BITSI'],
            ['nama' => 'Divisi Organisasi', 'urutan' => 3, 'komunitas' => 'SIBINER'],
            ['nama' => 'Media Komunikasi dan Publikasi (Medkom)', 'urutan' => 4, 'komunitas' => null],
            ['nama' => 'Ekonomi dan Kewirausahaan (Ekowir)', 'urutan' => 5, 'komunitas' => null],
        ];

        $jabatanBph = [
            ['Ketua Umum', 'utama'],
            ['Wakil Ketua Umum', 'utama'],
            ['Sekretaris Umum', 'utama'],
            ['Bendahara Umum', 'utama'],
        ];

        $jabatanDefault = [
            ['Ketua Divisi', 'utama'],
            ['Sekretaris Divisi', 'staf'],
            ['Anggota Divisi', 'anggota'],
        ];

        foreach ($divisi as $item) {
            $divisiModel = Divisi::query()->create([
                'periode_id' => $periode->id,
                'komunitas_id' => $item['komunitas'] !== null
                    ? Komunitas::idByKode($item['komunitas'])
                    : null,
                'nama' => $item['nama'],
                'urutan' => $item['urutan'],
            ]);

            foreach ($divisiModel->nama === 'BPH' ? $jabatanBph : $jabatanDefault as [$nama, $tingkat]) {
                Jabatan::query()->create([
                    'divisi_id' => $divisiModel->id,
                    'nama' => $nama,
                    'tingkat' => $tingkat,
                    'urutan' => Jabatan::query()->where('divisi_id', $divisiModel->id)->count() + 1,
                ]);
            }
        }
    }
}
