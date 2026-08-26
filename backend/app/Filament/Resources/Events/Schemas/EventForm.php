<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('judul')->required()->maxLength(255),
            Textarea::make('deskripsi')->columnSpanFull(),
            DateTimePicker::make('mulai')->required(),
            DateTimePicker::make('selesai'),
            TextInput::make('lokasi'),
            Select::make('komunitas_id')->relationship('komunitas', 'nama'),
            FileUpload::make('poster_path')
                ->disk('public')
                ->directory('events/posters')
                ->image()
                ->maxSize(5120),
            Select::make('status')
                ->options(['draft' => 'Draft', 'published' => 'Published', 'batal' => 'Batal'])
                ->default('draft'),
        ]);
    }
}
