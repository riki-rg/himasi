<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['member_id', 'komunitas_id', 'status', 'approved_by', 'disetujui_pada'])]
class KomunitasMember extends Model
{
    protected $table = 'komunitas_member';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'disetujui_pada' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class);
    }
}
