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
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('nim')
                    ->required(),
                TextInput::make('nama')
                    ->required(),
                TextInput::make('prodi'),
                TextInput::make('angkatan')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('no_hp'),
                Textarea::make('alamat')
                    ->columnSpanFull(),
                TextInput::make('foto_path'),
                TextInput::make('link_portofolio'),
                TextInput::make('link_instagram'),
                TextInput::make('status')
                    ->required()
                    ->default('aktif'),
            ]);
    }
}
