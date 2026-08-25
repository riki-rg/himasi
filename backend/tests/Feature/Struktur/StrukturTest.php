<?php

namespace Tests\Feature\Struktur;

use App\Models\Jabatan;
use App\Models\Komunitas;
use App\Models\Periode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class StrukturTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    public function test_admin_bisa_buat_divisi_dan_jabatan(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $periode = Periode::query()->first();
        $medkom = Komunitas::idByKode('HIMSI');

        $divisiId = $this->withToken($token)
            ->postJson("/api/v1/periodes/{$periode->id}/divisi", [
                'komunitas_id' => $medkom,
                'nama' => 'Divisi IT',
            ])
            ->assertStatus(201)
            ->assertJsonPath('nama', 'Divisi IT')
            ->json('id');

        $this->withToken($token)
            ->postJson("/api/v1/divisi/{$divisiId}/jabatan", ['nama' => 'Ketua', 'tingkat' => 'utama'])
            ->assertStatus(201)
            ->assertJsonPath('tingkat', 'utama');
    }

    public function test_anggota_biasa_tidak_bisa_kelola_struktur(): void
    {
        [,, $token] = $this->anggotaBiasa();

        $this->withToken($token)->getJson('/api/v1/periodes')->assertStatus(403);
    }

    public function test_periode_baru_otomatis_arsipkan_yang_lama(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');

        $response = $this->withToken($token)
            ->postJson('/api/v1/periodes', [
                'nama' => 'Kepengurusan 2027',
                'tanggal_mulai' => '2027-01-01',
                'tanggal_selesai' => '2027-12-31',
            ])->assertStatus(201);

        $this->assertSame('aktif', $response->json('status'));
        $this->assertSame(0, Periode::query()->where('status', 'aktif')->whereNot('nama', 'Kepengurusan 2027')->count());
        $this->assertSame(1, Periode::query()->where('status', 'arsip')->count());
    }

    public function test_arsipkan_dua_kali_409(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $periode = Periode::query()->first();

        $this->withToken($token)->postJson("/api/v1/periodes/{$periode->id}/arsipkan")->assertStatus(200);
        $this->withToken($token)->postJson("/api/v1/periodes/{$periode->id}/arsipkan")->assertStatus(409);
    }

    public function test_periode_arsip_read_only(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $periode = Periode::query()->first();
        $this->withToken($token)->postJson("/api/v1/periodes/{$periode->id}/arsipkan")->assertStatus(200);

        $this->withToken($token)
            ->postJson("/api/v1/periodes/{$periode->id}/divisi", [
                'komunitas_id' => Komunitas::idByKode('HIMSI'),
                'nama' => 'Divisi Baru',
            ])->assertStatus(409);
    }

    public function test_penugasan_duplikat_422(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $jabatan = Jabatan::query()->first();
        $anggota = $this->buatMemberLain();

        $payload = [
            'member_id' => $anggota->id,
            'jabatan_id' => $jabatan->id,
            'periode_id' => $jabatan->divisi->periode_id,
        ];

        $this->withToken($token)->postJson('/api/v1/penugasan', $payload)->assertStatus(201);
        $this->withToken($token)->postJson('/api/v1/penugasan', $payload)->assertStatus(422);
    }

    public function test_hapus_penugasan_tidak_menghapus_member(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $jabatan = Jabatan::query()->first();
        $anggota = $this->buatMemberLain();

        $penugasanId = $this->withToken($token)->postJson('/api/v1/penugasan', [
            'member_id' => $anggota->id,
            'jabatan_id' => $jabatan->id,
            'periode_id' => $jabatan->divisi->periode_id,
        ])->json('id');

        $this->withToken($token)->deleteJson("/api/v1/penugasan/{$penugasanId}")->assertStatus(204);

        $this->assertDatabaseHas('members', ['id' => $anggota->id]);
        $this->assertDatabaseMissing('penugasans', ['id' => $penugasanId]);
    }
}
