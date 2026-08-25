<?php

namespace Tests\Feature\Anggota;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    public function test_import_csv_sukses(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');

        $csv = "nim,nama,prodi,angkatan,email,no_hp,status\n"
            ."2301050001,Rizky Maulana,Sistem Informasi,2023,rizky@umku.ac.id,0812,aktif\n"
            ."2301050002,Alya Putri,,2023,,,alumni\n";

        $response = $this->withToken($token)
            ->post('/api/v1/anggota/import', [
                'file' => $this->fakeUpload($csv, 'anggota.csv', 'text/csv'),
            ], ['Accept' => 'application/json']);

        $response->assertStatus(200)
            ->assertJsonPath('berhasil', 2)
            ->assertJsonPath('gagal', 0);

        $this->assertDatabaseHas('members', ['nim' => '2301050001', 'nama' => 'Rizky Maulana']);
        $this->assertDatabaseHas('members', ['nim' => '2301050002', 'status' => 'alumni']);
    }

    public function test_import_baris_gagal_dilaporkan_per_baris(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        Member::query()->create(['nim' => '2301050099', 'nama' => 'Sudah Ada', 'angkatan' => '2023']);

        $csv = "nim,nama,prodi,angkatan,email,no_hp,status\n"
            ."2301050099,Duplikat NIM,,2023,,,,\n"
            .",Nama Kosong,,2023,,,,\n"
            ."2301050010,Valid,,2024,,,,\n";

        $response = $this->withToken($token)
            ->post('/api/v1/anggota/import', [
                'file' => $this->fakeUpload($csv, 'anggota.csv', 'text/csv'),
            ], ['Accept' => 'application/json']);

        $response->assertStatus(200)
            ->assertJsonPath('berhasil', 1)
            ->assertJsonPath('gagal', 2);

        $detail = $response->json('detail_gagal');
        $this->assertSame(2, $detail[0]['baris']);
        $this->assertSame(3, $detail[1]['baris']);
        $this->assertStringContainsString('terdaftar', $detail[0]['alasan']);
        $this->assertStringContainsString('NIM kosong', $detail[1]['alasan']);
    }

    public function test_export_csv_berisi_kolom_anggota(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $this->buatMemberLain('2401050022', 'Bima Pratama');

        $response = $this->withToken($token)
            ->get('/api/v1/anggota/export?format=csv');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $body = $response->streamedContent();
        $this->assertStringContainsString('2401050022', $body);
        $this->assertStringContainsString('Bima Pratama', $body);
    }

    public function test_export_xlsx_menghasilkan_file_binary(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        $this->buatMemberLain();

        $response = $this->withToken($token)->get('/api/v1/anggota/export?format=xlsx');

        $response->assertOk()->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        $this->assertGreaterThan(100, strlen($response->streamedContent()));
    }

    private function fakeUpload(string $content, string $filename, string $mime): File
    {
        $path = sys_get_temp_dir().'/'.$filename;
        file_put_contents($path, $content);

        return new File($filename, fopen($path, 'r'));
    }
}
