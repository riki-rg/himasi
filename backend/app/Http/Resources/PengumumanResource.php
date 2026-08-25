<?php

namespace App\Http\Resources;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pengumuman */
class PengumumanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'isi' => $this->isi,
            'prioritas' => $this->prioritas,
            'tayang_mulai' => $this->tayang_mulai?->toDateString(),
            'tayang_selesai' => $this->tayang_selesai?->toDateString(),
            'komunitas_id' => $this->komunitas_id,
        ];
    }
}
