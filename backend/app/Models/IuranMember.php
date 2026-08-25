<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['iuran_id', 'member_id', 'status', 'kas_id', 'lunas_pada'])]
class IuranMember extends Model
{
    protected $table = 'iuran_member';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lunas_pada' => 'datetime',
        ];
    }

    public function iuran(): BelongsTo
    {
        return $this->belongsTo(Iuran::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
