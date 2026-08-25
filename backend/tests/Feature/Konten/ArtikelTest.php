<?php

namespace Tests\Feature\Konten;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class ArtikelTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    public function test_draft_tidak_muncul_publik_lalu_muncul_setelah_publish(): void
    {
        [, , $tokenAdmin] = $this->akunDenganJabatan('Ketua Umum');

        $slug = $this->withToken($tokenAdmin)
            ->postJson('/api/v1/artikels', [
                'judul' => 'Workshop IoT Sukses Digelar',
                'konten' => 'Konten berita panjang...',
                'kategori' => 'Kegiatan',
            ])
            ->assertStatus(201)
            ->assertJsonPath('status', 'draft')
            ->json('slug');

        $this->getJson('/api/v1/publik/artikels')->assertJsonPath('meta.total', 0);
        $this->getJson("/api/v1/publik/artikels/{$slug}")->assertStatus(404);

        $this->withToken($tokenAdmin)
            ->putJson('/api/v1/artikels/1', ['status' => 'published'])
            ->assertStatus(200)
            ->assertJsonPath('published_at', fn ($v) => $v !== null);

        $this->getJson('/api/v1/publik/artikels')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.author.name', 'Pengurus');

        $this->getJson("/api/v1/publik/artikels/{$slug}")
            ->assertOk()
            ->assertJsonPath('judul', 'Workshop IoT Sukses Digelar');
    }

    public function test_anggota_biasa_tidak_bisa_crud_artikel(): void
    {
        [,, $token] = $this->anggotaBiasa();

        $this->withToken($token)->getJson('/api/v1/artikels')->assertStatus(403);
        $this->withToken($token)->postJson('/api/v1/artikels', [
            'judul' => 'x', 'konten' => 'y',
        ])->assertStatus(403);
    }

    public function test_ketua_divisi_staf_boleh_mengelola_konten(): void
    {
        [,, $token] = $this->akunDenganJabatan('Sekretaris Divisi');

        $this->withToken($token)
            ->postJson('/api/v1/artikels', [
                'judul' => 'Artikel dari Medkom',
                'konten' => 'Isi artikel.',
                'status' => 'published',
            ])->assertStatus(201);
    }

    public function test_slug_duplikat_diberi_suffix(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');

        $payload = ['judul' => 'Judul Sama', 'konten' => 'isi'];

        $slug1 = $this->withToken($token)->postJson('/api/v1/artikels', $payload)->json('slug');
        $slug2 = $this->withToken($token)->postJson('/api/v1/artikels', $payload)->json('slug');

        $this->assertNotSame($slug1, $slug2);
        $this->assertSame($slug1.'-2', $slug2);
    }

    public function test_filter_kategori_dan_pencarian_q(): void
    {
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');

        foreach ([['A', 'Prestasi'], ['B', 'Kegiatan']] as [$judul, $kategori]) {
            $this->withToken($token)->postJson('/api/v1/artikels', [
                'judul' => $judul,
                'konten' => "konten {$judul}",
                'kategori' => $kategori,
                'status' => 'published',
            ])->assertStatus(201);
        }

        $this->withToken($token)
            ->getJson('/api/v1/artikels?kategori=Prestasi')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
