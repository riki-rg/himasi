<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'judul', 'tanggal', 'jam_mulai', 'jam_selesai', 'tempat', 'agenda',
    'notulen', 'lampiran_path', 'komunitas_id', 'qr_secret', 'status', 'user_id',
])]
class Rapat extends Model
{
    protected $table = 'rapats';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class);
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(RapatMember::class);
    }
}
