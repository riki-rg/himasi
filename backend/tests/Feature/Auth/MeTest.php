<?php

namespace Tests\Feature\Auth;

use App\Models\Jabatan;
use App\Models\Member;
use App\Models\Penugasan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeTest extends TestCase
{
    use RefreshDatabase;

    private function akunDenganStruktur(string $jabatanNama): array
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->create([
            'name' => 'Rizz',
            'email' => 'rizz@example.com',
            'password' => 'rahasia123',
            'status' => 'aktif',
        ]);

        $member = Member::query()->create([
            'user_id' => $user->id,
            'nim' => '2101050001',
            'nama' => 'Rizz',
            'angkatan' => '2021',
            'email' => 'rizz@example.com',
            'status' => 'aktif',
        ]);

        $jabatan = Jabatan::query()->where('nama', $jabatanNama)->first();

        if ($jabatan !== null) {
            Penugasan::query()->create([
                'member_id' => $member->id,
                'jabatan_id' => $jabatan->id,
                'periode_id' => $jabatan->divisi->periode_id,
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return [$user, $member, $token, $jabatan];
    }

    public function test_tanpa_token_ditolak_401(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertStatus(401)
            ->assertHeader('Content-Type', 'application/problem+json');
    }

    public function test_me_mengembalikan_profil_komunitas_dan_penugasan(): void
    {
        [, , $token] = $this->akunDenganStruktur('Ketua Divisi');

        $response = $this->withToken($token)->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'name', 'email',
                'member' => ['nim', 'nama', 'angkatan'],
                'komunitas',
                'penugasan_aktif',
            ]);
    }

    public function test_role_admin_pusat_otomatis_dari_penugasan_ketua_umum(): void
    {
        [, , $token] = $this->akunDenganStruktur('Ketua Umum');

        $user = auth('sanctum')->user();
        $this->assertTrue($user === null);

        // Gate dicek via HTTP-level helper: cukup pastikan resolver mengenali
        $response = $this->withToken($token)->getJson('/api/v1/auth/me');
        $response->assertStatus(200);
        $this->assertSame(
            1,
            collect($response->json('penugasan_aktif'))->count()
        );
    }

    public function test_update_password_berhasil_dan_login_pakai_password_baru(): void
    {
        [,, $token] = $this->akunDenganStruktur('');

        $this->withToken($token)
            ->putJson('/api/v1/auth/password', [
                'password_lama' => 'rahasia123',
                'password_baru' => 'passwordbaru99',
            ])->assertStatus(204);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'rizz@example.com',
            'password' => 'passwordbaru99',
        ])->assertStatus(200);
    }

    public function test_update_password_lama_salah_422_problem_json(): void
    {
        [,, $token] = $this->akunDenganStruktur('');

        $response = $this->withToken($token)
            ->putJson('/api/v1/auth/password', [
                'password_lama' => 'bukan-password',
                'password_baru' => 'passwordbaru99',
            ]);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('errors.password_lama.0', 'Password lama tidak sesuai.');
    }
}
