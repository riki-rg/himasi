<?php

namespace Tests\Feature\Konten;

use App\Models\GaleriAlbum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\MenyiapkanStruktur;
use Tests\TestCase;

class GaleriTest extends TestCase
{
    use MenyiapkanStruktur, RefreshDatabase;

    public function test_album_crud_dan_multi_upload_foto(): void
    {
        Storage::fake('public');
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');

        $albumId = $this->withToken($token)
            ->postJson('/api/v1/galeri/albums', [
                'judul' => 'Workshop IoT',
                'deskripsi' => 'Dokumentasi sesi pertama',
            ])
            ->assertStatus(201)
            ->json('id');

        $fotos = $this->withToken($token)
            ->post("/api/v1/galeri/albums/{$albumId}/fotos", [
                'files' => [$this->fakeImage(), $this->fakeImage(), $this->fakeImage()],
                'caption' => 'Suasana workshop',
            ], ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJsonCount(3);

        Storage::disk('public')->assertExists($fotos->json('0.path'));
        $this->assertSame(1, $fotos->json('0.urutan'));
        $this->assertSame(3, $fotos->json('2.urutan'));
        $this->assertSame('Suasana workshop', $fotos->json('0.caption'));
        $this->assertNull($fotos->json('1.caption'));

        $album = $this->withToken($token)->getJson("/api/v1/publik/galeri/albums/{$albumId}");
        $cover = $album->json('cover_path');
        $this->assertNotNull($cover);
    }

    public function test_update_caption_urutan_dan_hapus_foto(): void
    {
        Storage::fake('public');
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        [$albumId, $fotoId] = $this->buatAlbumSatuFoto($token);

        $this->withToken($token)
            ->putJson("/api/v1/galeri/fotos/{$fotoId}", ['caption' => 'Baru', 'urutan' => 5])
            ->assertStatus(200)
            ->assertJsonPath('caption', 'Baru')
            ->assertJsonPath('urutan', 5);

        $path = $this->withToken($token)->getJson("/api/v1/galeri/albums/{$albumId}")->json('fotos.0.path');
        $this->withToken($token)->deleteJson("/api/v1/galeri/fotos/{$fotoId}")->assertStatus(204);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_hapus_album_ikut_menghapus_semua_foto(): void
    {
        Storage::fake('public');
        [,, $token] = $this->akunDenganJabatan('Ketua Umum');
        [$albumId] = $this->buatAlbumSatuFoto($token);

        $paths = GaleriAlbum::query()->findOrFail($albumId)->fotos->pluck('path')->all();

        $this->withToken($token)->deleteJson("/api/v1/galeri/albums/{$albumId}")->assertStatus(204);

        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
        $this->assertDatabaseMissing('galeri_albums', ['id' => $albumId]);
    }

    private function buatAlbumSatuFoto(string $token): array
    {
        $albumId = $this->withToken($token)
            ->postJson('/api/v1/galeri/albums', ['judul' => 'Album Test'])
            ->json('id');

        $fotoId = $this->withToken($token)
            ->post("/api/v1/galeri/albums/{$albumId}/fotos", [
                'files' => [$this->fakeImage()],
            ], ['Accept' => 'application/json'])
            ->json('0.id');

        return [$albumId, $fotoId];
    }

    private function fakeImage(): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $path = sys_get_temp_dir().'/galeri-'.uniqid().'.png';
        file_put_contents($path, $png);

        return new UploadedFile($path, 'foto.png', 'image/png', null, true);
    }
}
