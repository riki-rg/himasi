<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['divisi_id', 'nama', 'tingkat', 'urutan'])]
class Jabatan extends Model
{
    protected $table = 'jabatans';

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class);
    }

    public function penugasans(): HasMany
    {
        return $this->hasMany(Penugasan::class);
    }
}
