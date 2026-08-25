<?php

namespace Tests\Feature\Concerns;

use App\Models\Jabatan;
use App\Models\Komunitas;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Helper menyiapkan akun + penugasan untuk pengujian otorisasi.
 */
trait MenyiapkanStruktur
{
    /**
     * @return array{0: User, 1: Member, 2: string}
     */
    protected function akunDenganJabatan(string $jabatanNama, string $nim = '2101050001', ?string $komunitasKode = null): array
    {
        if (Komunitas::query()->count() === 0) {
            $this->seed(DatabaseSeeder::class);
        }

        $user = User::query()->create([
            'name' => 'Pengurus',
            'email' => "pengurus-{$nim}@example.com",
            'password' => 'rahasia123',
            'status' => 'aktif',
        ]);

        $member = Member::query()->create([
            'user_id' => $user->id,
            'nim' => $nim,
            'nama' => 'Pengurus',
            'angkatan' => '2021',
            'status' => 'aktif',
        ]);

        if ($jabatanNama !== '') {
            $jabatan = Jabatan::query()
                ->where('nama', $jabatanNama)
                ->when(
                    $komunitasKode !== null,
                    fn ($q) => $q->whereHas('divisi.komunitas', fn ($k) => $k->where('kode', $komunitasKode))
                )
                ->firstOrFail();

            DB::table('penugasans')->insert([
                'member_id' => $member->id,
                'jabatan_id' => $jabatan->id,
                'periode_id' => $jabatan->divisi->periode_id,
            ]);
        }

        return [$user, $member, $user->createToken('api')->plainTextToken];
    }

    /**
     * @return array{0: User, 1: Member, 2: string}
     */
    protected function anggotaBiasa(string $nim = '2201050077'): array
    {
        return $this->akunDenganJabatan('', $nim);
    }

    protected function buatMemberLain(string $nim = '2301050055', string $nama = 'Anggota Lain'): Member
    {
        return Member::query()->create([
            'nim' => $nim,
            'nama' => $nama,
            'angkatan' => substr($nim, 0, 4),
            'status' => 'aktif',
        ]);
    }
}
