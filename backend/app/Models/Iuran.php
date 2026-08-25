<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'jumlah', 'periode_id', 'komunitas_id', 'tenggat'])]
class Iuran extends Model
{
    protected $table = 'iurans';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tenggat' => 'date',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class);
    }

    public function tagihans(): HasMany
    {
        return $this->hasMany(IuranMember::class);
    }
}
