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
        return $schema->components([
            TextInput::make('judul')->required()->maxLength(255),
            Textarea::make('isi')->required()->columnSpanFull(),
            Select::make('prioritas')
                ->options(['normal' => 'Normal', 'penting' => 'Penting'])
                ->default('normal'),
            DatePicker::make('tayang_mulai')->label('Tayang mulai'),
            DatePicker::make('tayang_selesai')->label('Tayang sampai'),
            Select::make('komunitas_id')
                ->relationship('komunitas', 'nama')
                ->label('Untuk komunitas (kosong = semua)'),
        ]);
    }
}
