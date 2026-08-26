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
        return $schema
            ->components([
                Select::make('komunitas_id')
                    ->relationship('komunitas', 'id')
                    ->required(),
                Select::make('divisi_id')
                    ->relationship('divisi', 'id'),
                TextInput::make('nama')
                    ->required(),
                Textarea::make('deskripsi')
                    ->columnSpanFull(),
                TextInput::make('jadwal_hari'),
                TextInput::make('jadwal_jam'),
                TextInput::make('tempat'),
            ]);
    }
}
