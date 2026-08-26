<?php

namespace App\Filament\Resources\Members\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nim')
                    ->label('NIM')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('prodi')
                    ->label('Prodi'),
                TextInput::make('angkatan')
                    ->label('Angkatan')
                    ->required()
                    ->numeric()
                    ->length(4),
                TextInput::make('email')
                    ->email(),
                TextInput::make('no_hp')
                    ->label('No. HP'),
                Textarea::make('alamat')
                    ->columnSpanFull(),
                TextInput::make('link_portofolio')
                    ->label('Link Portofolio')
                    ->url(),
                TextInput::make('link_instagram')
                    ->label('Instagram'),
                Select::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                        'alumni' => 'Alumni',
                    ])
                    ->required()
                    ->default('aktif'),
            ]);
    }
}
