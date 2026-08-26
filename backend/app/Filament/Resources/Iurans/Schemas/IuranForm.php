<?php

namespace App\Filament\Resources\Iurans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IuranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required(),
                TextInput::make('jumlah')
                    ->required()
                    ->numeric(),
                Select::make('periode_id')
                    ->relationship('periode', 'id')
                    ->required(),
                Select::make('komunitas_id')
                    ->relationship('komunitas', 'id'),
                DatePicker::make('tenggat')
                    ->required(),
            ]);
    }
}
