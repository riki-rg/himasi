<?php

namespace Tests\Feature\Konten;

use App\Models\Komunitas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class EventPengumumanTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    public function test_event_publik_filter_mendatang_dan_komunitas(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $bitSi = Komunitas::idByKode('BITSI');
        $sibiner = Komunitas::idByKode('SIBINER');

        $this->buatEvent($token, ['judul' => 'Seminar', 'mulai' => now()->addDays(3), 'komunitas_id' => null]);
        $this->buatEvent($token, ['judul' => 'Kopdar BitSI', 'mulai' => now()->addDays(5), 'komunitas_id' => $bitSi]);
        $this->buatEvent($token, ['judul' => 'Lama', 'mulai' => now()->subDays(5), 'komunitas_id' => null]);
        $this->buatEvent($token, ['judul' => 'Dibatalkan', 'mulai' => now()->addDay(), 'status' => 'batal']);

        $mendatang = $this->withToken($token)
            ->getJson('/api/v1/publik/events?mendatang=true')
            ->assertOk();

        $this->assertSame(2, $mendatang->json('meta.total'));

        $bitSiOnly = $this->withToken($token)
            ->getJson('/api/v1/publik/events?mendatang=true&komunitas=BITSI')
            ->assertOk();

        $this->assertSame('Kopdar BitSI', $bitSiOnly->json('data.0.judul'));
    }

    public function test_pengumuman_hanya_masa_tayang_aktif_dan_penting_duluan(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');

        $this->buatPengumuman($token, ['judul' => 'Normal Aktif', 'prioritas' => 'normal']);
        $this->buatPengumuman($token, ['judul' => 'Penting Aktif', 'prioritas' => 'penting']);
        $this->buatPengumuman($token, [
            'judul' => 'Kadaluarsa',
            'tayang_mulai' => now()->subDays(10)->toDateString(),
            'tayang_selesai' => now()->subDays(2)->toDateString(),
        ]);

        $publik = $this->withToken($token)->getJson('/api/v1/publik/pengumumans')->assertOk();

        $judul = collect($publik->json())->pluck('judul')->values();
        $this->assertSame(['Penting Aktif', 'Normal Aktif'], $judul->all());
    }

    public function test_admin_list_menampilkan_semua_termasuk_kadaluarsa(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');

        $this->buatPengumuman($token, ['judul' => 'A']);
        $this->buatPengumuman($token, ['judul' => 'Kadaluarsa',
            'tayang_selesai' => now()->subDay()->toDateString()]);

        $this->withToken($token)->getJson('/api/v1/pengumumans')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    private function buatEvent(string $token, array $overrides = []): void
    {
        $this->withToken($token)->postJson('/api/v1/events', [
            'judul' => 'Event',
            'mulai' => now()->addDay(),
            'status' => 'published',
            ...$overrides,
        ])->assertStatus(201);
    }

    private function buatPengumuman(string $token, array $overrides = []): void
    {
        $this->withToken($token)->postJson('/api/v1/pengumumans', [
            'judul' => 'Judul',
            'isi' => 'Isi pengumuman',
            ...$overrides,
        ])->assertStatus(201);
    }
}
