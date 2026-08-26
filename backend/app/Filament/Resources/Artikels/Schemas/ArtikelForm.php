<?php

namespace App\Filament\Resources\Artikels\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ArtikelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('judul')->required()->maxLength(255),
            TextInput::make('slug')->unique(ignoreRecord: true),
            RichEditor::make('konten')->required()->columnSpanFull(),
            TextInput::make('kategori'),
            TagsInput::make('tags'),
            FileUpload::make('cover_path')
                ->disk('public')
                ->directory('artikels/covers')
                ->image()
                ->maxSize(5120),
            Select::make('status')
                ->options(['draft' => 'Draft', 'published' => 'Published'])
                ->default('draft'),
        ]);
    }
}
