<?php

namespace App\Filament\Resources\Rapats\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class RapatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('judul')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('tanggal')
                    ->required(),
                TimePicker::make('jam_mulai')
                    ->required(),
                TimePicker::make('jam_selesai'),
                TextInput::make('tempat'),
                Textarea::make('agenda')
                    ->columnSpanFull(),
                Textarea::make('notulen')
                    ->columnSpanFull(),
                FileUpload::make('lampiran_path')
                    ->label('Lampiran notulen (PDF ≤10MB)')
                    ->disk('public')
                    ->directory('rapat/lampiran')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240),
                Select::make('komunitas_id')
                    ->relationship('komunitas', 'nama')
                    ->label('Komunitas penyelenggara'),
                Select::make('status')
                    ->options([
                        'dijadwalkan' => 'Dijadwalkan',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ])
                    ->required()
                    ->default('dijadwalkan'),
            ]);
    }
}
