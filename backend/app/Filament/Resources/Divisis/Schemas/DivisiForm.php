<?php

namespace App\Filament\Resources\Divisis\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DivisiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('periode_id')
                    ->relationship('periode', 'id')
                    ->required(),
                Select::make('komunitas_id')
                    ->relationship('komunitas', 'id'),
                TextInput::make('nama')
                    ->required(),
                TextInput::make('urutan')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
