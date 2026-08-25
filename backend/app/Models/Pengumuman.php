<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['judul', 'isi', 'prioritas', 'tayang_mulai', 'tayang_selesai', 'komunitas_id'])]
class Pengumuman extends Model
{
    protected $table = 'pengumumans';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tayang_mulai' => 'date',
            'tayang_selesai' => 'date',
        ];
    }

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class);
    }
}
