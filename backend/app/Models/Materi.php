<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['kelas_id', 'judul', 'tipe', 'file_path', 'link_url', 'urutan'])]
class Materi extends Model
{
    protected $table = 'materis';

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }
}
