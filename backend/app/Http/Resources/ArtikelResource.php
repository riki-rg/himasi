<?php

namespace App\Http\Resources;

use App\Models\Artikel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Artikel */
class ArtikelResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'slug' => $this->slug,
            'konten' => $this->konten,
            'cover_path' => $this->cover_path,
            'kategori' => $this->kategori,
            'tags' => $this->tags ?? [],
            'status' => $this->status,
            'published_at' => $this->published_at?->toISOString(),
            'author' => $this->penulis ? [
                'id' => $this->penulis->id,
                'name' => $this->penulis->name,
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
