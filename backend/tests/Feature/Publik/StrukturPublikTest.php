<?php

namespace Tests\Feature\Publik;

use App\Models\Jabatan;
use App\Models\Member;
use App\Models\Penugasan;
use App\Models\Periode;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrukturPublikTest extends TestCase
{
    use RefreshDatabase;

    private function seedStruktur(): void
    {
        $this->seed(DatabaseSeeder::class);
    }

    public function test_tree_lengkap_dengan_pengurus(): void
    {
        $this->seedStruktur();

        $ketuaUmum = Jabatan::query()->where('nama', 'Ketua Umum')->first();
        $alumni = Member::query()->create([
            'nim' => '1801050001',
            'nama' => 'Alumni Tanpa Akun',
            'angkatan' => '2018',
        ]);
        Penugasan::query()->create([
            'member_id' => $alumni->id,
            'jabatan_id' => $ketuaUmum->id,
            'periode_id' => $ketuaUmum->divisi->periode_id,
        ]);

        $response = $this->getJson('/api/v1/publik/struktur');

        $response->assertOk()->assertJsonIsArray();

        $bph = collect($response->json())->firstWhere('divisi.nama', 'BPH');
        $this->assertNotNull($bph);

        $pengurus = collect($bph['jabatan'])
            ->firstWhere('nama', 'Ketua Umum')['pengurus'];

        $this->assertSame('Alumni Tanpa Akun', $pengurus[0]['nama']);
        $this->assertArrayNotHasKey('email', $pengurus[0]);
    }

    public function test_filter_komunitas_hanya_divisi_terkait(): void
    {
        $this->seedStruktur();

        $response = $this->getJson('/api/v1/publik/struktur?komunitas=BITSI');

        $divisis = collect($response->json())->pluck('divisi.nama');
        $this->assertSame(['Divisi Pengembangan Diri'], $divisis->values()->all());
    }

    public function test_fallback_periode_arsip_saat_tidak_ada_aktif(): void
    {
        $this->seedStruktur();
        Periode::query()->update(['status' => 'arsip']);

        $this->getJson('/api/v1/publik/struktur')
            ->assertOk()
            ->assertJsonIsArray();
    }
}
