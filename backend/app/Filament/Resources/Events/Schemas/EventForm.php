<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('judul')
                    ->required(),
                Textarea::make('deskripsi')
                    ->columnSpanFull(),
                TextInput::make('poster_path'),
                TextInput::make('lokasi'),
                DateTimePicker::make('mulai')
                    ->required(),
                DateTimePicker::make('selesai'),
                Select::make('komunitas_id')
                    ->relationship('komunitas', 'id'),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
            ]);
    }
}
