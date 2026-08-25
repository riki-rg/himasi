<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'tanggal_mulai', 'tanggal_selesai', 'status'])]
class Periode extends Model
{
    protected $table = 'periodes';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function divisis(): HasMany
    {
        return $this->hasMany(Divisi::class);
    }

    public static function aktif(): ?self
    {
        return static::query()->where('status', 'aktif')->first();
    }
}
