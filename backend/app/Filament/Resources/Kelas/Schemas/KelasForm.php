<?php

namespace App\Filament\Resources\Kelas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KelasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('komunitas_id')
                ->relationship('komunitas', 'nama')
                ->required(),
            TextInput::make('nama')->required()->maxLength(255),
            Textarea::make('deskripsi')->columnSpanFull(),
            Select::make('divisi_id')->relationship('divisi', 'nama'),
            TextInput::make('jadwal_hari')->label('Jadwal hari'),
            TextInput::make('jadwal_jam')->label('Jadwal jam'),
            TextInput::make('tempat'),
        ]);
    }
}
