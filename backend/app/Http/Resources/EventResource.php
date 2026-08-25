<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Event */
class EventResource extends JsonResource
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
            'poster_path' => $this->poster_path,
            'lokasi' => $this->lokasi,
            'mulai' => $this->mulai?->toISOString(),
            'selesai' => $this->selesai?->toISOString(),
            'komunitas_id' => $this->komunitas_id,
            'status' => $this->status,
        ];
    }
}
