<?php

namespace App\Http\Resources;

use App\Models\GaleriFoto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin GaleriFoto */
class GaleriFotoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'album_id' => $this->album_id,
            'path' => $this->path,
            'caption' => $this->caption,
            'urutan' => $this->urutan,
        ];
    }
}
