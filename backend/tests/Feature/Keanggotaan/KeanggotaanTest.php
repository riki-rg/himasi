<?php

namespace Tests\Feature\Keanggotaan;

use App\Models\Komunitas;
use App\Models\KomunitasMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class KeanggotaanTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    public function test_apply_mandiri_berstatus_pending(): void
    {
        [, $member, $token] = $this->anggotaBiasa();

        $response = $this->withToken($token)
            ->postJson('/api/v1/keanggotaan', ['komunitas' => 'BITSI']);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('komunitas.kode', 'BITSI');

        $this->assertDatabaseHas('komunitas_member', [
            'member_id' => $member->id,
            'status' => 'pending',
        ]);
    }

    public function test_apply_duplikat_409(): void
    {
        [, , $token] = $this->anggotaBiasa();

        $this->withToken($token)->postJson('/api/v1/keanggotaan', ['komunitas' => 'BITSI'])->assertStatus(201);

        $response = $this->withToken($token)->postJson('/api/v1/keanggotaan', ['komunitas' => 'BITSI']);

        $response->assertStatus(409)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('title', 'Konflik state');
    }

    public function test_ketua_bitsi_bisa_approve_pendaftar_bitsi(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010');

        [, $pendaftar] = $this->anggotaBiasa('2201050077');
        $km = KomunitasMember::query()->create([
            'member_id' => $pendaftar->id,
            'komunitas_id' => Komunitas::idByKode('BITSI'),
            'status' => 'pending',
        ]);

        $response = $this->withToken($tokenKetua)
            ->patchJson("/api/v1/keanggotaan/{$km->id}", ['status' => 'disetujui']);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'disetujui')
            ->assertJsonPath('disetujui_pada', fn ($v) => $v !== null);
    }

    public function test_ketua_bitsi_tidak_bisa_proses_sibiner_403(): void
    {
        [,, $tokenKetuaBitSi] = $this->akunDenganJabatan('Ketua Divisi', '2101050010');

        [, $anggotaSibiner, $tokenAnggota] = $this->anggotaBiasa('2201050088');
        $kmId = KomunitasMember::query()->create([
            'member_id' => $anggotaSibiner->id,
            'komunitas_id' => Komunitas::idByKode('SIBINER'),
            'status' => 'pending',
        ])->id;

        $response = $this->withToken($tokenKetuaBitSi)
            ->patchJson("/api/v1/keanggotaan/{$kmId}", ['status' => 'ditolak']);

        $response->assertStatus(403)
            ->assertHeader('Content-Type', 'application/problem+json');

        $this->withToken($tokenAnggota)->deleteJson("/api/v1/keanggotaan/{$kmId}")->assertStatus(403);
    }

    public function test_input_manual_oleh_ketua_langsung_disetujui(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050020', 'SIBINER');

        $calonAnggota = $this->buatMemberLain('2301050055', 'Calon Sibiner');

        $response = $this->withToken($tokenKetua)
            ->postJson('/api/v1/keanggotaan', [
                'komunitas' => 'SIBINER',
                'member_id' => $calonAnggota->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'disetujui')
            ->assertJsonPath('member.nim', '2301050055');
    }

    public function test_input_manual_lintas_komunitas_ditolak_403(): void
    {
        [,, $tokenKetuaBitSi] = $this->akunDenganJabatan('Ketua Divisi', '2101050030');
        $target = $this->buatMemberLain('2301050066', 'Target Sibiner');

        $this->withToken($tokenKetuaBitSi)
            ->postJson('/api/v1/keanggotaan', [
                'komunitas' => 'SIBINER',
                'member_id' => $target->id,
            ])->assertStatus(403);
    }

    public function test_delete_keanggotaan_oleh_pengurus(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050040');

        [, $anggota] = $this->anggotaBiasa('2201050099');
        $kmId = KomunitasMember::query()->create([
            'member_id' => $anggota->id,
            'komunitas_id' => Komunitas::idByKode('BITSI'),
            'status' => 'pending',
        ])->id;

        $this->withToken($tokenKetua)
            ->deleteJson("/api/v1/keanggotaan/{$kmId}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('komunitas_member', ['id' => $kmId]);
    }
}
