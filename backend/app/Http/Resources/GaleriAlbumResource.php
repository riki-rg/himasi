<?php

namespace App\Http\Resources;

use App\Models\GaleriAlbum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin GaleriAlbum */
class GaleriAlbumResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi,
            'cover_path' => $this->cover_path,
            'event_id' => $this->event_id,
            'jumlah_foto' => $this->whenCounted('fotos', fn () => $this->fotos_count, fn () => $this->fotos->count()),
            'fotos' => GaleriFotoResource::collection($this->whenLoaded('fotos')),
        ];
    }
}
