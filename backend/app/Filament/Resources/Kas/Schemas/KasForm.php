<?php

namespace App\Filament\Resources\Kas\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal')
                    ->required(),
                Select::make('tipe')
                    ->options([
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('nominal')
                    ->numeric()
                    ->required()
                    ->prefix('Rp'),
                Select::make('kas_kategori_id')
                    ->relationship('kategori', 'nama')
                    ->label('Kategori'),
                TextInput::make('keterangan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('periode_id')
                    ->relationship('periode', 'nama')
                    ->required(),
                Select::make('member_id')
                    ->relationship('member', 'nama')
                    ->label('Terkait anggota (opsional)')
                    ->searchable()
                    ->nullable(),
                FileUpload::make('bukti_path')
                    ->label('Bukti nota/foto ≤5MB')
                    ->disk('public')
                    ->directory('kas/bukti')
                    ->image()
                    ->maxSize(5120),
            ]);
    }
}
