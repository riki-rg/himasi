<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['periode_id', 'komunitas_id', 'nama', 'urutan'])]
class Divisi extends Model
{
    protected $table = 'divisis';

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class);
    }

    public function jabatans(): HasMany
    {
        return $this->hasMany(Jabatan::class);
    }
}
