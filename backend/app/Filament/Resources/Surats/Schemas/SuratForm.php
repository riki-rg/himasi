<?php

namespace App\Filament\Resources\Surats\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SuratForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('periode_id')
                    ->relationship('periode', 'nama')
                    ->label('Periode')
                    ->required(),
                Select::make('jenis')
                    ->options([
                        'masuk' => 'Surat Masuk',
                        'keluar' => 'Surat Keluar',
                    ])
                    ->required()
                    ->live(),
                Select::make('surat_template_id')
                    ->relationship('template', 'nama_jenis')
                    ->label('Template penomoran (wajib utk surat keluar)')
                    ->visible(fn ($get) => $get('jenis') === 'keluar'),
                TextInput::make('nomor_surat')
                    ->disabled()
                    ->hint('otomatis dari template untuk surat keluar')
                    ->dehydrated(false),
                DatePicker::make('tanggal_surat')
                    ->label('Tanggal surat')
                    ->required(),
                TextInput::make('pihak')
                    ->required()
                    ->maxLength(255),
                TextInput::make('perihal')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('file_path')
                    ->label('Scan / lampiran (≤10MB)')
                    ->disk('public')
                    ->directory('surat/scan')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(10240),
                Textarea::make('disposisi')
                    ->columnSpanFull(),
            ]);
    }
}
