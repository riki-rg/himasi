<?php

namespace App\Filament\Resources\SuratTemplates\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SuratTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('periode_id')
                    ->required()
                    ->numeric(),
                TextInput::make('nama_jenis')
                    ->required(),
                TextInput::make('format')
                    ->required(),
                TextInput::make('counter')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
