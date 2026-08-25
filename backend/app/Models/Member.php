<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'nim', 'nama', 'prodi', 'angkatan', 'email', 'no_hp',
    'alamat', 'foto_path', 'link_portofolio', 'link_instagram', 'status',
])]
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $table = 'members';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'angkatan' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function penugasans(): HasMany
    {
        return $this->hasMany(Penugasan::class);
    }

    public function keanggotaanKomunitas(): HasMany
    {
        return $this->hasMany(KomunitasMember::class);
    }
}
