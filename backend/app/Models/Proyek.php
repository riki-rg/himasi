<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['judul', 'slug', 'deskripsi', 'thumbnail_path', 'link_demo', 'link_repo', 'teknologi', 'status', 'published_at', 'pembuat_id', 'divisi_id', 'komunitas_id'])]
class Proyek extends Model
{
    protected $table = 'proyeks';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'teknologi' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'pembuat_id');
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class);
    }
}
