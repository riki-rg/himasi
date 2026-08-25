<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'tipe_default'])]
class KasKategori extends Model
{
    protected $table = 'kas_kategoris';

    public function transaksis(): HasMany
    {
        return $this->hasMany(Kas::class, 'kas_kategori_id');
    }
}
