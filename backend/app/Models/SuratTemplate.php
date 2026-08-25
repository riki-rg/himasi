<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['periode_id', 'nama_jenis', 'format', 'counter'])]
class SuratTemplate extends Model
{
    protected $table = 'surat_templates';

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function surats(): HasMany
    {
        return $this->hasMany(Surat::class);
    }
}
