<?php

namespace App\Filament\Resources\Members\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('prodi'),
                TextColumn::make('angkatan')
                    ->sortable(),
                TextColumn::make('email'),
                TextColumn::make('no_hp')
                    ->label('No. HP'),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'aktif',
                        'warning' => 'nonaktif',
                        'gray' => 'alumni',
                    ]),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
