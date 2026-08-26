<?php

namespace App\Filament\Resources\Proyeks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProyekForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('komunitas_id')
                    ->relationship('komunitas', 'id')
                    ->required(),
                Select::make('pembuat_id')
                    ->relationship('pembuat', 'id')
                    ->required(),
                Select::make('divisi_id')
                    ->relationship('divisi', 'id'),
                TextInput::make('judul')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('deskripsi')
                    ->columnSpanFull(),
                TextInput::make('thumbnail_path'),
                TextInput::make('link_demo'),
                TextInput::make('link_repo'),
                Textarea::make('teknologi')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                DateTimePicker::make('published_at'),
            ]);
    }
}
