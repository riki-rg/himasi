<?php

namespace App\Filament\Resources\Surats\Tables;

use App\Models\SuratStatusLog;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class SuratsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periode.id')
                    ->searchable(),
                TextColumn::make('surat_template_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('jenis')
                    ->searchable(),
                TextColumn::make('nomor_surat')
                    ->searchable(),
                TextColumn::make('tanggal_surat')
                    ->date()
                    ->sortable(),
                TextColumn::make('pihak')
                    ->searchable(),
                TextColumn::make('perihal')
                    ->searchable(),
                TextColumn::make('file_path')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('setStatus')
                    ->label('Set Status')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn ($record) => $record->jenis === 'keluar' && $record->nextStatus() !== null)
                    ->form([
                        Placeholder::make('info')
                            ->content('Lanjut ke: '.$record->nextStatus()),
                    ])
                    ->action(function ($record) {
                        $next = $record->nextStatus();
                        if ($next === null) {
                            return;
                        }
                        DB::transaction(function () use ($record, $next) {
                            $record->update(['status' => $next]);
                            SuratStatusLog::create([
                                'surat_id' => $record->id,
                                'status' => $next,
                                'user_id' => auth()->id(),
                            ]);
                        });
                    }),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
