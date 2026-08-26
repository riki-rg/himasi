<?php

namespace App\Filament\Resources\Rapats\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PesertaRelationManager extends RelationManager
{
    protected static string $relationship = 'peserta';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.nama'),
                TextColumn::make('member.nim')->label('NIM'),
                TextColumn::make('kehadiran')
                    ->badge()
                    ->colors([
                        'success' => 'hadir',
                        'warning' => 'izin',
                        'danger' => 'tidak',
                    ]),
                TextColumn::make('waktu_absen')
                    ->label('Waktu absen')
                    ->dateTime('d M Y H:i'),
                TextColumn::make('catatan'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('+ Tambah Peserta'),
            ])
            ->actions([
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Belum ada peserta');
    }
}
