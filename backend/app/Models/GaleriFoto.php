<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['album_id', 'path', 'caption', 'urutan'])]
class GaleriFoto extends Model
{
    protected $table = 'galeri_fotos';

    public function album(): BelongsTo
    {
        return $this->belongsTo(GaleriAlbum::class, 'album_id');
    }
}
