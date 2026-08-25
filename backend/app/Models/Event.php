<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['judul', 'deskripsi', 'poster_path', 'lokasi', 'mulai', 'selesai', 'komunitas_id', 'status'])]
class Event extends Model
{
    protected $table = 'events';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mulai' => 'datetime',
            'selesai' => 'datetime',
        ];
    }

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class);
    }

    public function albums(): HasMany
    {
        return $this->hasMany(GaleriAlbum::class);
    }
}
