<?php

namespace App\Filament\Resources\Proyeks\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProyekForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('komunitas_id')
                ->relationship('komunitas', 'nama')
                ->required(),
            TextInput::make('judul')->required()->maxLength(255),
            Textarea::make('deskripsi')->columnSpanFull(),
            TagsInput::make('teknologi'),
            TextInput::make('link_demo')->url(),
            TextInput::make('link_repo')->url(),
            Select::make('pembuat_id')
                ->relationship('pembuat', 'nama')
                ->searchable()
                ->required(),
            FileUpload::make('thumbnail_path')
                ->disk('public')
                ->directory('proyeks/thumbnails')
                ->image()
                ->maxSize(5120),
            Select::make('status')
                ->options(['draft' => 'Draft', 'published' => 'Published'])
                ->default('draft'),
        ]);
    }
}
