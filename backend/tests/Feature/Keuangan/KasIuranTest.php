<?php

namespace Tests\Feature\Keuangan;

use App\Models\Komunitas;
use App\Models\Periode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class KasIuranTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    private function png(): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        file_put_contents($p = sys_get_temp_dir().'/bukti-'.uniqid().'.png', $bytes);

        return new UploadedFile($p, 'nota.png', 'image/png', null, true);
    }

    public function test_anggota_biasa_403_bendahara_bisa(): void
    {
        [,, $tokenAnggota] = $this->anggotaBiasa();
        [, , $tokenBendahara] = $this->akunDenganJabatan('Bendahara Umum');

        $this->withToken($tokenAnggota)->getJson('/api/v1/kas')->assertStatus(403);
        $this->resetGuard();
        $this->withToken($tokenBendahara)->getJson('/api/v1/kas')->assertStatus(200);
    }

    public function test_transaksi_dengan_presisi_decimal_dan_bukti(): void
    {
        Storage::fake('public');
        [,, $token] = $this->akunDenganJabatan('Bendahara Umum');
        $periodeId = Periode::query()->first()->id;

        $response = $this->withToken($token)
            ->post('/api/v1/kas', [
                'tanggal' => '2026-08-01',
                'tipe' => 'pemasukan',
                'nominal' => '150000.75',
                'keterangan' => 'Iuran Agustus',
                'kategori' => 'Iuran',
                'periode_id' => $periodeId,
                'bukti_foto' => $this->png(),
            ], ['Accept' => 'application/json']);

        $response->assertStatus(201)->assertJsonPath('nominal', '150000.75');
        Storage::disk('public')->assertExists($response->json('bukti_path'));

        $this->assertDatabaseHas('kas', ['nominal' => '150000.75', 'kas_kategori_id' => 1]);
    }

    public function test_rekap_saldo_masuk_keluar_dan_breakdown_kategori(): void
    {
        [,, $token] = $this->akunDenganJabatan('Bendahara Umum');
        [, , $tokenPengurusLain] = $this->akunDenganJabatan('Sekretaris Divisi', '2101050007');
        $periodeId = Periode::query()->first()->id;

        foreach ([
            ['tanggal' => '2026-08-01', 'tipe' => 'pemasukan', 'nominal' => '500000', 'kategori' => 'Sponsor'],
            ['tanggal' => '2026-08-05', 'tipe' => 'pengeluaran', 'nominal' => '200000', 'kategori' => 'Konsumsi'],
            ['tanggal' => '2026-09-01', 'tipe' => 'pemasukan', 'nominal' => '100000', 'kategori' => 'Donasi'],
        ] as $row) {
            $this->withToken($token)->postJson('/api/v1/kas', [...$row,
                'keterangan' => 'trx', 'periode_id' => $periodeId,
            ])->assertStatus(201);
        }

        $this->resetGuard();

        $rekap = $this->withToken($tokenPengurusLain)
            ->getJson("/api/v1/kas/rekap?periode_id={$periodeId}&kelompok=kategori")
            ->assertOk();

        $this->assertEqualsWithDelta(600000.0, $rekap->json('total_pemasukan'), 0.01);
        $this->assertEqualsWithDelta(200000.0, $rekap->json('total_pengeluaran'), 0.01);
        $this->assertEqualsWithDelta(400000.0, $rekap->json('saldo'), 0.01);
        $this->assertCount(3, $rekap->json('breakdown'));
    }

    public function test_export_laporan_keuangan_csv_berisi_total_dan_saldo(): void
    {
        [,, $token] = $this->akunDenganJabatan('Bendahara Umum');
        $periodeId = Periode::query()->first()->id;

        $this->withToken($token)->postJson('/api/v1/kas', [
            'tanggal' => '2026-08-01', 'tipe' => 'pemasukan', 'nominal' => '300000',
            'keterangan' => 'Kas masuk', 'kategori' => 'Donasi', 'periode_id' => $periodeId,
        ])->assertStatus(201);

        $response = $this->withToken($token)->get('/api/v1/kas/export?format=csv');
        $response->assertOk();

        $body = $response->streamedContent();
        $this->assertStringContainsString('300000', $body);
        $this->assertStringContainsString('Total Pemasukan', $body);
        $this->assertStringContainsString('Saldo', $body);
    }

    public function test_iuran_generate_tagihan_otomatis_anggota_komunitas(): void
    {
        [,, $token] = $this->akunDenganJabatan('Bendahara Umum');
        $this->anggotaKomunitas('BITSI', '2201050001');
        $this->anggotaKomunitas('BITSI', '2201050002');
        $this->anggotaKomunitas('SIBINER', '2201050099');
        $periodeId = Periode::query()->first()->id;

        $response = $this->withToken($token)->postJson('/api/v1/iuran', [
            'nama' => 'Kas Bulanan Februari',
            'jumlah' => 25000,
            'periode_id' => $periodeId,
            'komunitas_id' => Komunitas::idByKode('BITSI'),
            'tenggat' => '2026-02-28',
        ])->assertStatus(201);

        $this->assertSame(2, $response->json('tagihan_dibuat'));
    }

    public function test_lunasi_tagihan_atomik_buat_transaksi_kas(): void
    {
        [,, $token] = $this->akunDenganJabatan('Bendahara Umum');
        [$anggota] = $this->anggotaKomunitas('BITSI', '2201050010');
        $periodeId = Periode::query()->first()->id;

        $iuranId = $this->withToken($token)->postJson('/api/v1/iuran', [
            'nama' => 'Kas Maret', 'jumlah' => 25000, 'periode_id' => $periodeId,
            'komunitas_id' => Komunitas::idByKode('BITSI'), 'tenggat' => '2026-03-31',
        ])->json('id');

        $tagihanId = $this->withToken($token)->getJson("/api/v1/iuran/{$iuranId}/tagihan")->json('0.id');

        $lunas = $this->withToken($token)->postJson("/api/v1/iuran/tagihan/{$tagihanId}/lunasi");
        $lunas->assertStatus(200)
            ->assertJsonPath('status', 'lunas')
            ->assertJsonPath('kas_id', fn ($v) => $v !== null);

        $this->assertDatabaseHas('kas', [
            'tipe' => 'pemasukan',
            'nominal' => '25000.00',
            'member_id' => $anggota->member->id,
        ]);
        $this->assertDatabaseHas('iuran_member', ['id' => $tagihanId, 'status' => 'lunas']);

        $this->resetGuard();
        $this->withToken($token)->postJson("/api/v1/iuran/tagihan/{$tagihanId}/lunasi")->assertStatus(409);
    }
}
