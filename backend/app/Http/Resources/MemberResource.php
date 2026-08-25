<?php

namespace App\Http\Resources;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Member */
class MemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nim' => $this->nim,
            'nama' => $this->nama,
            'prodi' => $this->prodi,
            'angkatan' => (int) $this->angkatan,
            'email' => $this->email,
            'no_hp' => $this->no_hp,
            'alamat' => $this->alamat,
            'foto_path' => $this->foto_path,
            'link_portofolio' => $this->link_portofolio,
            'link_instagram' => $this->link_instagram,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
