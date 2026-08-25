<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tanggal', 'tipe', 'nominal', 'kas_kategori_id', 'periode_id',
    'keterangan', 'bukti_path', 'member_id', 'user_id',
])]
class Kas extends Model
{
    protected $table = 'kas';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'tanggal' => 'date',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KasKategori::class, 'kas_kategori_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
