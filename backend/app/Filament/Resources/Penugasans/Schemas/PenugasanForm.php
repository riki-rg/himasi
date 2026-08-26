<?php

namespace App\Filament\Resources\Penugasans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PenugasanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('member_id')
                    ->relationship('member', 'id')
                    ->required(),
                Select::make('jabatan_id')
                    ->relationship('jabatan', 'id')
                    ->required(),
                Select::make('periode_id')
                    ->relationship('periode', 'id')
                    ->required(),
            ]);
    }
}
