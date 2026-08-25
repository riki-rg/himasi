<?php

namespace Tests\Feature\Rapat;

use App\Models\Komunitas;
use App\Models\RapatMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class RapatTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    public function test_ketua_buat_rapat_qr_secret_tak_ekspos_dan_peserta_terdaftar(): void
    {
        [, $anggota] = $this->anggotaKomunitas('BITSI', '2201050001');
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');

        $response = $this->withToken($tokenKetua)
            ->postJson('/api/v1/rapat', [
                'judul' => 'Rapat Persiapan Workshop',
                'tanggal' => today()->toDateString(),
                'jam_mulai' => '16:00',
                'jam_selesai' => '17:30',
                'tempat' => 'Lab SI',
                'agenda' => 'Evaluasi program kerja',
                'komunitas_id' => Komunitas::idByKode('BITSI'),
                'member_ids' => [$anggota->id],
            ])->assertStatus(201);

        $json = $response->json();
        $this->assertArrayNotHasKey('qr_secret', $json);
        $this->assertSame('16:00', $json['jam_mulai']);

        $this->assertDatabaseHas('rapat_member', ['rapat_id' => $json['id'], 'member_id' => $anggota->id]);
    }

    public function test_anggota_non_bitsi_detail_403_anggota_bitsi_200(): void
    {
        [, , $tokenSibiner] = $this->anggotaKomunitas('SIBINER', '2201050090');
        [, , $tokenBitSi] = $this->anggotaKomunitas('BITSI', '2201050091');

        $rapat = $this->buatRapat();

        $this->withToken($tokenSibiner)
            ->getJson("/api/v1/rapat/{$rapat->id}")
            ->assertStatus(403)
            ->assertHeader('Content-Type', 'application/problem+json');

        $this->resetGuard();

        $this->withToken($tokenBitSi)
            ->getJson("/api/v1/rapat/{$rapat->id}")
            ->assertStatus(200)
            ->assertJsonPath('judul', 'Rapat Mingguan');
    }

    public function test_simpan_notulen_dengan_lampiran_pdf(): void
    {
        Storage::fake('public');
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');
        $rapat = $this->buatRapat();

        $pdf = base64_decode('JVBERi0xLjQKJeLjz9MKMSAwIG9iajw8L1R5cGUvQ2F0YWxvZz4+ZW5kb2Jq');
        file_put_contents($tmp = sys_get_temp_dir().'/notulen.pdf', $pdf);

        $response = $this->withToken($tokenKetua)
            ->put("/api/v1/rapat/{$rapat->id}/notulen", [
                'notulen' => 'Workshop ditunda minggu depan.',
                'lampiran_file' => new UploadedFile($tmp, 'notulen.pdf', 'application/pdf', null, true),
            ], ['Accept' => 'application/json']);

        $response->assertStatus(200)->assertJsonPath('lampiran_path', fn ($p) => $p !== null);
        Storage::disk('public')->assertExists($response->json('lampiran_path'));
    }

    public function test_update_rapat_sinkron_peserta(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');
        [$a, $b] = [$this->buatMemberLain('2301050011'), $this->buatMemberLain('2301050012')];
        $rapat = $this->buatRapat([$a->id]);

        $this->withToken($tokenKetua)
            ->putJson("/api/v1/rapat/{$rapat->id}", ['member_ids' => [$a->id, $b->id]])
            ->assertStatus(200);

        $this->assertDatabaseHas('rapat_member', ['rapat_id' => $rapat->id, 'member_id' => $b->id]);
    }

    public function test_hapus_rapat(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');
        $rapat = $this->buatRapat();

        $this->withToken($tokenKetua)->deleteJson("/api/v1/rapat/{$rapat->id}")->assertStatus(204);

        $this->assertDatabaseMissing('rapats', ['id' => $rapat->id]);
        $this->assertSame(0, RapatMember::query()->where('rapat_id', $rapat->id)->count());
    }
}
