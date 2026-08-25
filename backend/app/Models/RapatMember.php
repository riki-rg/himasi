<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rapat_id', 'member_id', 'kehadiran', 'waktu_absen', 'catatan'])]
class RapatMember extends Model
{
    protected $table = 'rapat_member';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu_absen' => 'datetime',
        ];
    }

    public function rapat(): BelongsTo
    {
        return $this->belongsTo(Rapat::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
