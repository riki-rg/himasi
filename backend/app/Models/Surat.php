<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'periode_id', 'surat_template_id', 'jenis', 'nomor_surat', 'tanggal_surat',
    'pihak', 'perihal', 'file_path', 'disposisi', 'status', 'created_by',
])]
class Surat extends Model
{
    protected $table = 'surats';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SuratTemplate::class, 'surat_template_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function nextStatus(): ?string
    {
        $urutan = ['draft', 'review', 'disetujui', 'terkirim'];
        $posisi = array_search($this->status ?? 'draft', $urutan, true);

        return $urutan[$posisi + 1] ?? null;
    }
}
