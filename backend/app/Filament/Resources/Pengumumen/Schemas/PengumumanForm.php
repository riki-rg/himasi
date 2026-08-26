<?php

namespace App\Filament\Resources\Pengumumen\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PengumumanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('judul')
                    ->required(),
                Textarea::make('isi')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('prioritas')
                    ->required()
                    ->default('normal'),
                DatePicker::make('tayang_mulai'),
                DatePicker::make('tayang_selesai'),
                Select::make('komunitas_id')
                    ->relationship('komunitas', 'id'),
            ]);
    }
}
