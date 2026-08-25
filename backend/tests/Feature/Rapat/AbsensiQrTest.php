<?php

namespace Tests\Feature\Rapat;

use App\Models\RapatMember;
use App\Services\QrPresensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class AbsensiQrTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    private QrPresensi $qr;

    protected function setUp(): void
    {
        parent::setUp();
        $this->qr = app(QrPresensi::class);
        Carbon::setTestNow(today()->setTime(16, 5));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_pengurus_ambil_qr_dan_member_absen_sukses(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');
        [$userAnggota] = $this->anggotaKomunitas('BITSI');
        $rapat = $this->buatRapat([$userAnggota->member->id]);

        $payload = $this->withToken($tokenKetua)
            ->getJson("/api/v1/rapat/{$rapat->id}/qr")
            ->assertStatus(200)
            ->json();

        $this->assertSame(2, substr_count($payload['payload'], '|'));
        $this->assertCount(3, explode('|', $payload['payload']));
        $this->assertGreaterThanOrEqual(1, $payload['expires_in']);
        $this->assertLessThanOrEqual(60, $payload['expires_in']);

        $response = $this->actingAs($userAnggota)
            ->postJson("/api/v1/rapat/{$rapat->id}/absen", ['token' => $payload['payload']]);

        $response->assertStatus(200)->assertJsonPath('kehadiran', 'hadir');

        $this->assertDatabaseHas('rapat_member', [
            'rapat_id' => $rapat->id,
            'member_id' => $userAnggota->member->id,
            'kehadiran' => 'hadir',
        ]);
    }

    public function test_rotasi_tanpa_write_db(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');
        $rapat = $this->buatRapat();

        $p1 = $this->withToken($tokenKetua)->getJson("/api/v1/rapat/{$rapat->id}/qr")->json('payload');

        Carbon::setTestNow(now()->addSeconds(61));
        $p2 = $this->withToken($tokenKetua)->getJson("/api/v1/rapat/{$rapat->id}/qr")->json('payload');

        $this->assertNotSame($p1, $p2);
        $this->assertDatabaseHas('rapats', [
            'id' => $rapat->id,
            'qr_secret' => $rapat->fresh()->qr_secret,
        ]);
    }

    public function test_token_lama_kedaluwarsa_410(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');
        [$userAnggota] = $this->anggotaKomunitas('BITSI');
        $rapat = $this->buatRapat([$userAnggota->member->id]);

        $tokenLama = $this->withToken($tokenKetua)->getJson("/api/v1/rapat/{$rapat->id}/qr")->json('payload');

        Carbon::setTestNow(now()->addSeconds(120));

        $response = $this->actingAs($userAnggota)
            ->postJson("/api/v1/rapat/{$rapat->id}/absen", ['token' => $tokenLama]);

        $response->assertStatus(410)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('type', fn ($t) => str_ends_with($t, '/problems/qr-expired'));
    }

    public function test_sudah_absen_409(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');
        [$userAnggota] = $this->anggotaKomunitas('BITSI');
        $rapat = $this->buatRapat([$userAnggota->member->id]);

        $token = $this->withToken($tokenKetua)->getJson("/api/v1/rapat/{$rapat->id}/qr")->json('payload');

        $this->actingAs($userAnggota)->postJson("/api/v1/rapat/{$rapat->id}/absen", ['token' => $token])->assertStatus(200);

        $response = $this->actingAs($userAnggota)
            ->postJson("/api/v1/rapat/{$rapat->id}/absen", ['token' => $token]);

        $response->assertStatus(409)
            ->assertJsonPath('type', fn ($t) => str_ends_with($t, '/problems/sudah-absen'));
    }

    public function test_token_rapat_lain_422(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');
        [$userAnggota] = $this->anggotaKomunitas('BITSI');
        $rapatA = $this->buatRapat([$userAnggota->member->id]);
        $rapatB = $this->buatRapat([], ['judul' => 'Rapat Lain']);

        $tokenB = $this->withToken($tokenKetua)->getJson("/api/v1/rapat/{$rapatB->id}/qr")->json('payload');

        $response = $this->actingAs($userAnggota)
            ->postJson("/api/v1/rapat/{$rapatA->id}/absen", ['token' => $tokenB]);

        $response->assertStatus(422);
    }

    public function test_absen_di_luar_hari_rapat_410(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');
        [$userAnggota] = $this->anggotaKomunitas('BITSI');
        $rapatBesok = $this->buatRapat([$userAnggota->member->id], ['tanggal' => today()->addDay()]);

        $token = $this->withToken($tokenKetua)->getJson("/api/v1/rapat/{$rapatBesok->id}/qr")->json('payload');

        $this->actingAs($userAnggota)
            ->postJson("/api/v1/rapat/{$rapatBesok->id}/absen", ['token' => $token])
            ->assertStatus(410);
    }

    public function test_rekap_kehadiran(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');
        [$h1] = $this->anggotaKomunitas('BITSI', '2201050001');
        [$h2] = $this->anggotaKomunitas('BITSI', '2201050002');
        [$izin] = $this->anggotaKomunitas('BITSI', '2201050003');
        $tidak = $this->buatMemberLain('2201050004', 'Bolos');

        $rapat = $this->buatRapat([
            $h1->member->id, $h2->member->id, $izin->member->id, $tidak->id,
        ]);

        RapatMember::query()->where('rapat_id', $rapat->id)->where('member_id', $h1->member->id)
            ->update(['kehadiran' => 'hadir', 'waktu_absen' => now()]);
        RapatMember::query()->where('rapat_id', $rapat->id)->where('member_id', $h2->member->id)
            ->update(['kehadiran' => 'hadir']);
        RapatMember::query()->where('rapat_id', $rapat->id)->where('member_id', $izin->member->id)
            ->update(['kehadiran' => 'izin', 'catatan' => 'Ada kuliah']);

        $rekap = $this->withToken($tokenKetua)->getJson("/api/v1/rapat/{$rapat->id}/rekap");

        $rekap->assertStatus(200)
            ->assertJsonPath('total_peserta', 4)
            ->assertJsonPath('hadir', 2)
            ->assertJsonPath('tidak_hadir', 0)
            ->assertJsonPath('izin', 1)
            ->assertJsonPath('persentase', fn ($v) => abs($v - 50.0) < 0.01);
    }
}
