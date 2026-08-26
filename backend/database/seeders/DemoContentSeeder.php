<?php

namespace Database\Seeders;

use App\Models\Artikel;
use App\Models\Event;
use App\Models\Jabatan;
use App\Models\Kas;
use App\Models\KasKategori;
use App\Models\Komunitas;
use App\Models\Member;
use App\Models\Pengumuman;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Proyek;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo content untuk preview UI — jalankan manual:
 *   php artisan db:seed --class=DemoContentSeeder
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Panel',
            'email' => 'panel@umku.id',
            'password' => 'rahasia123',
            'status' => 'aktif',
        ]);

        $ketua = Member::query()->create([
            'user_id' => $admin->id,
            'nim' => '2001050009',
            'nama' => 'Rizz Pratama',
            'prodi' => 'Sistem Informasi',
            'angkatan' => '2020',
            'link_instagram' => 'https://instagram.com/himsiumku',
        ]);

        $jabatan = Jabatan::query()->where('nama', 'Ketua Umum')->first();
        Penugasan::query()->create([
            'member_id' => $ketua->id,
            'jabatan_id' => $jabatan->id,
            'periode_id' => $jabatan->divisi->periode_id,
        ]);

        Pengumuman::query()->create([
            'judul' => 'Workshop IoT daftar sekarang — kuota 30 orang!',
            'isi' => 'Pendaftaran dibuka sampai akhir bulan. Hubungi kadiv pengembangan diri.',
            'prioritas' => 'penting',
            'tayang_mulai' => today(),
            'komunitas_id' => Komunitas::idByKode('BITSI'),
        ]);

        foreach ([
            ['Lomba UI/UX HIMSI Cup 2026', 'Prestasi'],
            ['Recap Kopdar BitSI #12: Intro ESP32', 'Kegiatan'],
        ] as [$judul, $kategori]) {
            Artikel::query()->create([
                'user_id' => $admin->id,
                'judul' => $judul,
                'slug' => Str::slug($judul),
                'konten' => "Isi berita {$judul}. Panitia melaporkan antusiasme tinggi dari peserta.",
                'kategori' => $kategori,
                'status' => 'published',
                'published_at' => now()->subDays(rand(1, 5)),
            ]);
        }

        Event::query()->create([
            'judul' => 'Seminar Karier Sistem Informasi',
            'deskripsi' => 'Belajar dari praktisi industri tentang jenjang karier SI.',
            'lokasi' => 'Auditorium UMKU',
            'mulai' => now()->addDays(12)->setHour(13),
            'selesai' => now()->addDays(12)->setHour(16),
            'status' => 'published',
        ]);
        Event::query()->create([
            'judul' => 'Diskusi Buku: Laskar Pelangi',
            'lokasi' => 'Perpustakaan',
            'mulai' => now()->addDays(6)->setHour(19),
            'status' => 'published',
            'komunitas_id' => Komunitas::idByKode('SIBINER'),
        ]);

        $kategoriIuran = KasKategori::where('nama', 'Iuran')->first();
        $kategoriKonsumsi = KasKategori::where('nama', 'Konsumsi')->first();
        $periodeId = Periode::aktif()->id;

        foreach (range(1, 8) as $bulan) {
            Kas::query()->create([
                'tanggal' => now()->setMonth($bulan)->setDay(5),
                'tipe' => 'pemasukan',
                'nominal' => 450000 + $bulan * 15000,
                'kas_kategori_id' => $kategoriIuran->id,
                'periode_id' => $periodeId,
                'keterangan' => "Iuran bulan ke-{$bulan}",
                'user_id' => $admin->id,
            ]);
            if ($bulan % 2 === 0) {
                Kas::query()->create([
                    'tanggal' => now()->setMonth($bulan)->setDay(15),
                    'tipe' => 'pengeluaran',
                    'nominal' => 120000 + $bulan * 5000,
                    'kas_kategori_id' => $kategoriKonsumsi->id,
                    'periode_id' => $periodeId,
                    'keterangan' => "Konsumsi rapat bulan ke-{$bulan}",
                    'user_id' => $admin->id,
                ]);
            }
        }

        foreach ([
            ['Dashboard Monitoring Air', 'esp8266', 'iot'],
            ['Landing Page Desa Wisata', 'laravel, tailwind', 'web'],
        ] as [$judul, $teknologi, $tag]) {
            Proyek::query()->create([
                'komunitas_id' => Komunitas::idByKode('BITSI'),
                'pembuat_id' => $ketua->id,
                'judul' => $judul,
                'slug' => Str::slug($judul),
                'deskripsi' => 'Proyek showcase anggota BitSI.',
                'teknologi' => array_map('trim', explode(',', $teknologi)),
                'status' => 'published',
                'published_at' => now(),
            ]);
        }
    }
}
