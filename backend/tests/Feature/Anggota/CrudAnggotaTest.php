<?php

namespace Tests\Feature\Anggota;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class CrudAnggotaTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    public function test_tanpa_login_ditolak_401(): void
    {
        $this->getJson('/api/v1/anggota')
            ->assertStatus(401)
            ->assertHeader('Content-Type', 'application/problem+json');
    }

    public function test_anggota_biasa_ditolak_403(): void
    {
        [,, $token] = $this->anggotaBiasa();

        $this->withToken($token)
            ->getJson('/api/v1/anggota')
            ->assertStatus(403)
            ->assertJsonPath('title', 'Akses ditolak');
    }

    public function test_ketua_umum_admin_pusat_bisa_akses(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $this->buatMemberLain();

        $response = $this->withToken($token)->getJson('/api/v1/anggota');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['nim', 'nama', 'angkatan', 'status']],
                'meta' => ['current_page', 'last_page', 'total'],
                'links',
            ]);
        $this->assertSame(2, $response->json('meta.total'));
    }

    public function test_search_q_dan_filter_status(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $this->buatMemberLain('2301050001', 'Rizky Maulana');
        $this->buatMemberLain('2301050002', 'Alya Putri');

        $hasil = $this->withToken($token)
            ->getJson('/api/v1/anggota?q=rizky')
            ->assertOk();

        $this->assertSame(1, $hasil->json('meta.total'));
        $this->assertSame('Rizky Maulana', $hasil->json('data.0.nama'));

        $this->withToken($token)
            ->getJson('/api/v1/anggota?angkatan=2301&status=aktif')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_per_page_dibatasi_maksimal_100(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $this->buatMemberLain();

        $this->withToken($token)
            ->getJson('/api/v1/anggota?per_page=500')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 100);
    }

    public function test_store_sukses_dan_nim_duplikat_422(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Divisi');

        $payload = [
            'nim' => '2401050010',
            'nama' => 'Citra Dewi',
            'angkatan' => 2024,
            'link_portofolio' => 'https://citra.dev',
        ];

        $this->withToken($token)
            ->postJson('/api/v1/anggota', $payload)
            ->assertStatus(201)
            ->assertJsonPath('nama', 'Citra Dewi')
            ->assertJsonPath('status', 'aktif');

        $response = $this->withToken($token)->postJson('/api/v1/anggota', $payload);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonStructure(['errors' => ['nim']]);
    }

    public function test_update_link_portofolio_muncul_di_detail(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $member = $this->buatMemberLain();

        $this->withToken($token)
            ->putJson("/api/v1/anggota/{$member->id}", [
                'link_portofolio' => 'https://baru.dev',
            ])->assertStatus(200);

        $this->withToken($token)
            ->getJson("/api/v1/anggota/{$member->id}")
            ->assertOk()
            ->assertJsonPath('link_portofolio', 'https://baru.dev')
            ->assertJsonPath('nim', $member->nim);
    }

    public function test_delete_anggota(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $member = $this->buatMemberLain();

        $this->withToken($token)
            ->deleteJson("/api/v1/anggota/{$member->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('members', ['id' => $member->id]);
    }
}
