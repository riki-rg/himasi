<?php

namespace Tests\Feature\Auth;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private function buatAkun(string $status = 'aktif'): User
    {
        $user = User::query()->create([
            'name' => 'Rizz',
            'email' => 'rizz@example.com',
            'password' => 'rahasia123',
            'status' => $status,
        ]);

        Member::query()->create([
            'user_id' => $user->id,
            'nim' => '2101050001',
            'nama' => 'Rizz',
            'angkatan' => '2021',
            'email' => 'rizz@example.com',
            'status' => 'aktif',
        ]);

        return $user;
    }

    public function test_login_sukses_mengembalikan_token(): void
    {
        $this->buatAkun();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'rizz@example.com',
            'password' => 'rahasia123',
        ])->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    public function test_kredensial_salah_401_problem_json(): void
    {
        $this->buatAkun();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'rizz@example.com',
            'password' => 'salahbanget',
        ]);

        $response->assertStatus(401)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('title', 'Belum terautentikasi');
    }

    public function test_akun_pending_ditolak_dengan_423(): void
    {
        $this->buatAkun('pending');

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'rizz@example.com',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(423)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('type', fn (string $type) => str_ends_with($type, '/problems/account-pending'));
    }

    public function test_logout_mencabut_token(): void
    {
        $user = $this->buatAkun();
        $token = $user->createToken('api')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(204);

        $this->assertSame(0, $user->tokens()->count());

        $this->postJson('/api/v1/auth/login', [
            'email' => 'rizz@example.com',
            'password' => 'rahasia123',
        ])->assertStatus(200);
    }
}
