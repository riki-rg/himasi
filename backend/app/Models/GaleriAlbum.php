<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['judul', 'deskripsi', 'cover_path', 'event_id'])]
class GaleriAlbum extends Model
{
    protected $table = 'galeri_albums';

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(GaleriFoto::class, 'album_id')->orderBy('urutan');
    }
}
