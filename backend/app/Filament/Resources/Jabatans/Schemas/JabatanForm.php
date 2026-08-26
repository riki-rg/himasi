<?php

namespace App\Filament\Resources\Jabatans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JabatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('divisi_id')
                    ->relationship('divisi', 'id')
                    ->required(),
                TextInput::make('nama')
                    ->required(),
                TextInput::make('tingkat')
                    ->required()
                    ->default('staf'),
                TextInput::make('urutan')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
