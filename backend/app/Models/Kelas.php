<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['komunitas_id', 'divisi_id', 'nama', 'deskripsi', 'jadwal_hari', 'jadwal_jam', 'tempat'])]
class Kelas extends Model
{
    protected $table = 'kelass';

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class);
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class);
    }

    public function materis(): HasMany
    {
        return $this->hasMany(Materi::class)->orderBy('urutan');
    }
}
