<?php

namespace Tests\Feature\Surat;

use App\Models\Periode;
use App\Models\Surat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class PenomoranSuratTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    private function buatTemplate(string $token, array $overrides = []): array
    {
        $periode = Periode::query()->first();

        return [
            'periode_id' => $periode->id,
            'nama_jenis' => 'proposal',
            'format' => '{urut}/HIMSI/UMKU/{romawi}/{tahun}',
            ...$overrides,
        ];
    }

    public function test_surat_keluar_pertama_dinomori_otomatis(): void
    {
        [,, $token] = $this->akunDenganJabatan('Sekretaris Umum');
        $template = $this->buatTemplate($token);

        $templateId = $this->withToken($token)
            ->postJson('/api/v1/surat/templates', $template)
            ->assertStatus(201)
            ->json('id');

        $surat = $this->withToken($token)->postJson('/api/v1/surat', [
            'jenis' => 'keluar',
            'tanggal_surat' => '2026-08-15',
            'pihak' => 'Fakultas Teknik',
            'perihal' => 'Proposal seminar',
            'periode_id' => $template['periode_id'],
            'surat_template_id' => $templateId,
        ])->assertStatus(201);

        $this->assertSame('001/HIMSI/UMKU/VIII/2026', $surat->json('nomor_surat'));
        $this->assertSame('draft', $surat->json('status'));

        $kedua = $this->withToken($token)->postJson('/api/v1/surat', [
            'jenis' => 'keluar',
            'tanggal_surat' => '2026-08-20',
            'pihak' => 'Panitia Lomba',
            'perihal' => 'Proposal lomba',
            'periode_id' => $template['periode_id'],
            'surat_template_id' => $templateId,
        ])->assertStatus(201);

        $this->assertSame('002/HIMSI/UMKU/VIII/2026', $kedua->json('nomor_surat'));
    }

    public function test_keluar_tanpa_template_422(): void
    {
        [,, $token] = $this->akunDenganJabatan('Sekretaris Umum');
        $periodeId = Periode::query()->first()->id;

        $response = $this->withToken($token)->postJson('/api/v1/surat', [
            'jenis' => 'keluar',
            'tanggal_surat' => '2026-08-15',
            'pihak' => 'X',
            'perihal' => 'Y',
            'periode_id' => $periodeId,
        ]);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('errors.surat_template_id.0', fn ($v) => str_contains($v, 'template'));
    }

    public function test_edit_format_tidak_mengubah_nomor_lama(): void
    {
        [,, $token] = $this->akunDenganJabatan('Sekretaris Umum');
        $template = $this->buatTemplate($token);

        $templateId = $this->withToken($token)->postJson('/api/v1/surat/templates', $template)->json('id');
        $nomorLama = $this->withToken($token)->postJson('/api/v1/surat', [
            'jenis' => 'keluar',
            'tanggal_surat' => '2026-08-15',
            'pihak' => 'P',
            'perihal' => 'H',
            'periode_id' => $template['periode_id'],
            'surat_template_id' => $templateId,
        ])->json('nomor_surat');

        $this->withToken($token)
            ->putJson("/api/v1/surat/templates/{$templateId}", ['format' => '{urut}/BARU/{tahun}'])
            ->assertStatus(200);

        $this->assertDatabaseHas('surats', ['nomor_surat' => $nomorLama]);
        $nomorBaru = $this->withToken($token)->postJson('/api/v1/surat', [
            'jenis' => 'keluar',
            'tanggal_surat' => '2026-08-16',
            'pihak' => 'P2',
            'perihal' => 'H2',
            'periode_id' => $template['periode_id'],
            'surat_template_id' => $templateId,
        ])->json('nomor_surat');

        $this->assertSame('002/BARU/2026', $nomorBaru);
        $this->assertSame(2, Surat::query()->count());
    }

    public function test_template_nama_jenis_duplikat_dalam_periode_422(): void
    {
        [,, $token] = $this->akunDenganJabatan('Sekretaris Umum');
        $template = $this->buatTemplate($token);

        $this->withToken($token)->postJson('/api/v1/surat/templates', $template)->assertStatus(201);
        $this->withToken($token)->postJson('/api/v1/surat/templates', $template)->assertStatus(422);
    }
}
