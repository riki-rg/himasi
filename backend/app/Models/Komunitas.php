<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kode', 'nama', 'deskripsi', 'logo_path'])]
class Komunitas extends Model
{
    protected $table = 'komunitas';

    public function divisis(): HasMany
    {
        return $this->hasMany(Divisi::class);
    }

    public function anggota(): HasMany
    {
        return $this->hasMany(KomunitasMember::class);
    }

    public static function idByKode(string $kode): ?int
    {
        return static::query()->where('kode', $kode)->value('id');
    }
}
