<?php

namespace Tests\Feature\Auth;

use App\Models\KomunitasMember;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_sukses_membuat_akun_pending_dan_member(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'nim' => '2101050001',
            'nama' => 'Rizz',
            'prodi' => 'Sistem Informasi',
            'angkatan' => 2021,
            'email' => 'rizz@example.com',
            'no_hp' => '081234567890',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('nim', '2101050001')
            ->assertJsonPath('nama', 'Rizz')
            ->assertJsonPath('status', 'aktif');

        $this->assertDatabaseHas('users', [
            'email' => 'rizz@example.com',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('members', ['nim' => '2101050001', 'user_id' => User::first()->id]);
    }

    public function test_register_dengan_komunitas_otomatis_apply_pending(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->postJson('/api/v1/auth/register', [
            'nim' => '2201050099',
            'nama' => 'Bima',
            'angkatan' => 2022,
            'email' => 'bima@example.com',
            'password' => 'rahasia123',
            'komunitas' => 'BITSI',
        ])->assertStatus(201);

        $km = KomunitasMember::query()->first();
        $this->assertSame('pending', $km->status);
        $this->assertTrue($km->komunitas->kode === 'BITSI');
    }

    public function test_nim_duplikat_mengembalikan_422_problem_json(): void
    {
        User::query()->create([
            'name' => 'Lama',
            'email' => 'lama@example.com',
            'password' => 'rahasia123',
            'status' => 'aktif',
        ]);

        $this->postJson('/api/v1/auth/register', [
            'nim' => '2101050001',
            'nama' => 'Baru',
            'angkatan' => 2021,
            'email' => 'lama@example.com',
            'password' => 'rahasia123',
        ]);

        // Email duplikat juga → 422 dengan format problem+json
        $memberNim = Member::query()->create([
            'nim' => '2101050001',
            'nama' => 'Existing',
            'angkatan' => '2021',
            'status' => 'aktif',
        ]);

        $this->assertNotNull($memberNim);

        $response = $this->postJson('/api/v1/auth/register', [
            'nim' => '2101050001',
            'nama' => 'Duplikat',
            'angkatan' => 2022,
            'email' => 'baru@example.com',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonStructure(['type', 'title', 'status', 'errors']);
    }
}
