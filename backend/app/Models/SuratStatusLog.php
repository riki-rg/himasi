<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['surat_id', 'status', 'catatan', 'user_id'])]
class SuratStatusLog extends Model
{
    protected $table = 'surat_status_logs';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
