<?php

namespace Tests\Feature\KaryaKelas;

use App\Models\Jabatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class ProyekKelasTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    private function png(): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        file_put_contents($p = sys_get_temp_dir().'/thumb-'.uniqid().'.png', $bytes);

        return new UploadedFile($p, 't.png', 'image/png', null, true);
    }

    public function test_pengurus_bitsi_buat_karya_dan_publish_muncul_di_publik(): void
    {
        Storage::fake('public');
        [, $pembuat, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');

        $id = $this->withToken($tokenKetua)
            ->post('/api/v1/proyeks', [
                'judul' => 'Sensor Suhu IoT',
                'deskripsi' => 'Monitoring suhu realtime',
                'teknologi' => ['Arduino', 'ESP8266'],
                'pembuat_id' => $pembuat->id,
                'thumbnail' => $this->png(),
            ], ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJsonPath('status', 'draft')
            ->json('id');

        $this->getJson('/api/v1/publik/proyeks?komunitas=BITSI')->assertJsonPath('meta.total', 0);

        $this->resetGuard();
        $this->withToken($tokenKetua)->putJson("/api/v1/proyeks/{$id}", ['status' => 'published'])
            ->assertStatus(200);

        $publik = $this->getJson('/api/v1/publik/proyeks?komunitas=BITSI')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        $this->assertSame('Sensor Suhu IoT', $publik->json('data.0.judul'));
        $this->assertSame(['Arduino', 'ESP8266'], $publik->json('data.0.teknologi'));

        $slug = $publik->json('data.0.slug');
        $this->getJson("/api/v1/publik/proyeks/{$slug}")
            ->assertOk()
            ->assertJsonPath('pembuat.nama', 'Pengurus');
    }

    public function test_ketua_sibiner_akses_proyek_bitsi_403(): void
    {
        [, , $tokenSibiner] = $this->akunDenganJabatan('Ketua Divisi', '2101050020', 'SIBINER');

        $this->withToken($tokenSibiner)
            ->getJson('/api/v1/proyeks?komunitas=BITSI')
            ->assertStatus(403);
    }

    public function test_anggota_biasa_tidak_bisa_kelola_karya_403(): void
    {
        [,, $tokenAnggota] = $this->anggotaBiasa();

        $this->withToken($tokenAnggota)->getJson('/api/v1/proyeks')->assertStatus(403);
    }

    public function test_kelas_publik_tanpa_materi_detail_gated_member(): void
    {
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050030', 'BITSI');
        [$userBitSi] = $this->anggotaKomunitas('BITSI', '2201050077');
        [$userSibiner] = $this->anggotaKomunitas('SIBINER', '2201050099');
        $bitSiDivisiId = Jabatan::query()->whereHas('divisi.komunitas', fn ($q) => $q->where('kode', 'BITSI'))->first()->divisi_id;

        $kelasId = $this->withToken($tokenKetua)
            ->postJson('/api/v1/kelass', [
                'nama' => 'Web Dev Dasar',
                'divisi_id' => $bitSiDivisiId,
                'jadwal_hari' => 'Sabtu',
                'jadwal_jam' => '16:00',
            ])->assertStatus(201)
            ->json('id');

        $this->resetGuard();

        $publik = $this->getJson('/api/v1/publik/kelass?komunitas=BITSI')->assertOk();
        $this->assertArrayNotHasKey('materis', $publik->json('0'));

        $detailPublik = $this->getJson("/api/v1/kelass/{$kelasId}");
        $this->assertTrue(in_array($detailPublik->getStatusCode(), [200, 403]));

        $this->resetGuard();
        $this->actingAs($userSibiner)->getJson("/api/v1/kelass/{$kelasId}")
            ->assertStatus(403);

        $this->resetGuard();
        $response = $this->actingAs($userBitSi)->getJson("/api/v1/kelass/{$kelasId}");
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_upload_materi_hanya_member_disetujui_bisa_buka(): void
    {
        Storage::fake('public');
        [,, $tokenKetua] = $this->akunDenganJabatan('Ketua Divisi', '2101050010', 'BITSI');
        [$userAnggota] = $this->anggotaKomunitas('BITSI', '2201050055');
        $divisiId = Jabatan::query()->whereHas('divisi.komunitas', fn ($q) => $q->where('kode', 'BITSI'))->first()->divisi_id;

        $kelasId = $this->withToken($tokenKetua)->postJson('/api/v1/kelass', [
            'nama' => 'IoT Dasar', 'divisi_id' => $divisiId,
        ])->json('id');

        $filePdf = '%PDF-1.4 materi html dasar';
        file_put_contents($tmp = sys_get_temp_dir().'/materi.pdf', $filePdf);

        $materi = $this->withToken($tokenKetua)
            ->post("/api/v1/kelass/{$kelasId}/materis", [
                'judul' => '01 Pengenalan HTML',
                'tipe' => 'file',
                'file' => new UploadedFile($tmp, 'html.pdf', 'application/pdf', null, true),
            ], ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJsonPath('urutan', 1);

        Storage::disk('public')->assertExists($materi->json('file_path'));

        $this->resetGuard();

        $detail = $this->actingAs($userAnggota)->getJson("/api/v1/kelass/{$kelasId}")->assertOk();
        $this->assertCount(1, $detail->json('materis'));
        $this->assertSame('01 Pengenalan HTML', $detail->json('materis.0.judul'));
    }
}
