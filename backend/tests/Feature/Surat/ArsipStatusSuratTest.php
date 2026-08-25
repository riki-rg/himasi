<?php

namespace Tests\Feature\Surat;

use App\Models\Periode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class ArsipStatusSuratTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    private function pdfUpload(): UploadedFile
    {
        $pdf = '%PDF-1.4 konten scan surat';
        file_put_contents($tmp = sys_get_temp_dir().'/scan-'.uniqid().'.pdf', $pdf);

        return new UploadedFile($tmp, 'surat.pdf', 'application/pdf', null, true);
    }

    public function test_surat_masuk_tersimpan_dengan_scan(): void
    {
        Storage::fake('public');
        [,, $token] = $this->akunDenganJabatan('Sekretaris Umum');
        $periodeId = Periode::query()->first()->id;

        $response = $this->withToken($token)
            ->post('/api/v1/surat', [
                'jenis' => 'masuk',
                'tanggal_surat' => '2026-08-10',
                'pihak' => 'Rektorat UMKU',
                'perihal' => 'Undangan wisuda',
                'periode_id' => $periodeId,
                'file_scan' => $this->pdfUpload(),
            ], ['Accept' => 'application/json']);

        $response->assertStatus(201)->assertJsonPath('nomor_surat', null);
        Storage::disk('public')->assertExists($response->json('file_path'));
    }

    public function test_pencarian_q_mencocokkan_perihal_dan_filter_jenis(): void
    {
        [,, $token] = $this->akunDenganJabatan('Sekretaris Umum');
        $periodeId = Periode::query()->first()->id;
        $templateId = $this->withToken($token)->postJson('/api/v1/surat/templates', [
            'periode_id' => $periodeId,
            'nama_jenis' => 'proposal',
            'format' => '{urut}/HIMSI/UMKU/{romawi}/{tahun}',
        ])->json('id');

        foreach ([
            ['jenis' => 'masuk', 'perihal' => 'Undangan wisuda', 'pihak' => 'Rektorat'],
            ['jenis' => 'keluar', 'perihal' => 'Proposal kegiatan', 'pihak' => 'BEM'],
        ] as $s) {
            $this->withToken($token)->postJson('/api/v1/surat', [
                ...$s,
                'tanggal_surat' => '2026-08-11',
                'periode_id' => $periodeId,
                'surat_template_id' => $s['jenis'] === 'keluar' ? $templateId : null,
            ])->assertStatus(201);
        }

        $this->withToken($token)->getJson('/api/v1/surat?q=wisuda')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.pihak', 'Rektorat');

        $this->withToken($token)->getJson('/api/v1/surat?jenis=keluar')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_anggota_biasa_403_sekretaris_divisi_boleh(): void
    {
        [,, $tokenAnggota] = $this->anggotaBiasa();
        [, , $tokenStaf] = $this->akunDenganJabatan('Sekretaris Divisi');

        $this->withToken($tokenAnggota)->getJson('/api/v1/surat')->assertStatus(403);

        $this->resetGuard();

        $this->withToken($tokenStaf)->getJson('/api/v1/surat')->assertStatus(200);
    }

    private function buatSuratKeluar(string $token): int
    {
        $templateId = $this->withToken($token)->postJson('/api/v1/surat/templates', [
            'periode_id' => Periode::query()->first()->id,
            'nama_jenis' => 'undangan',
            'format' => '{urut}/HIMSI/UMKU/{romawi}/{tahun}',
        ])->json('id');

        return $this->withToken($token)->postJson('/api/v1/surat', [
            'jenis' => 'keluar',
            'tanggal_surat' => '2026-08-15',
            'pihak' => 'Mahasiswa',
            'perihal' => 'Undangan rapat',
            'periode_id' => Periode::query()->first()->id,
            'surat_template_id' => $templateId,
        ])->json('id');
    }

    public function test_alur_status_hanya_maju_berurutan_dan_tercatat(): void
    {
        [,, $token] = $this->akunDenganJabatan('Sekretaris Umum');
        $id = $this->buatSuratKeluar($token);

        $this->withToken($token)->postJson("/api/v1/surat/{$id}/status", ['status' => 'terkirim'])
            ->assertStatus(409);

        foreach (['review', 'disetujui', 'terkirim'] as $status) {
            $this->withToken($token)
                ->postJson("/api/v1/surat/{$id}/status", ['status' => $status])
                ->assertStatus(200)
                ->assertJsonPath('status', $status);
        }

        $this->withToken($token)->postJson("/api/v1/surat/{$id}/status", ['status' => 'review'])
            ->assertStatus(409);

        $logs = $this->withToken($token)->getJson("/api/v1/surat/{$id}/logs")->assertOk();
        $this->assertCount(4, $logs->json());
        $this->assertSame('draft', $logs->json('0.status'));
        $this->assertSame('Pengurus', $logs->json('3.oleh'));
        $this->assertNotNull($logs->json('3.pada'));
    }

    public function test_status_surat_masuk_ditolak_409(): void
    {
        [,, $token] = $this->akunDenganJabatan('Sekretaris Umum');
        $id = $this->withToken($token)->postJson('/api/v1/surat', [
            'jenis' => 'masuk',
            'tanggal_surat' => '2026-08-10',
            'pihak' => 'Rektorat',
            'perihal' => 'Undangan',
            'periode_id' => Periode::query()->first()->id,
        ])->json('id');

        $this->withToken($token)->postJson("/api/v1/surat/{$id}/status", ['status' => 'review'])
            ->assertStatus(409);
    }
}
