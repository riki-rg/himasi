<?php

namespace App\Filament\Resources\Iurans\RelationManagers;

use App\Models\Kas;
use App\Models\KasKategori;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TagihansRelationManager extends RelationManager
{
    protected static string $relationship = 'tagihans';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.nama'),
                TextColumn::make('member.nim')
                    ->label('NIM'),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'danger' => 'belum',
                        'success' => 'lunas',
                    ]),
                TextColumn::make('lunas_pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('lunasi')
                    ->label('Tandai Lunas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Model $record) => $record->status === 'belum')
                    ->requiresConfirmation()
                    ->modalDescription('Transaksi kas pemasukan akan dibuat otomatis.')
                    ->action(function (Model $record) {
                        DB::transaction(function () use ($record) {
                            $iuran = $record->iuran;

                            $kas = Kas::query()->create([
                                'tanggal' => today(),
                                'tipe' => 'pemasukan',
                                'nominal' => $iuran->jumlah,
                                'kas_kategori_id' => KasKategori::query()->firstOrCreate(
                                    ['nama' => 'Iuran'],
                                    ['tipe_default' => 'pemasukan']
                                )->id,
                                'periode_id' => $iuran->periode_id,
                                'keterangan' => "Pelunasan iuran: {$iuran->nama} ({$record->member->nama})",
                                'member_id' => $record->member_id,
                                'user_id' => auth()->id(),
                            ]);

                            $record->update([
                                'status' => 'lunas',
                                'kas_id' => $kas->id,
                                'lunas_pada' => now(),
                            ]);
                        });
                    }),
            ])
            ->headerActions([])
            ->emptyStateHeading('Belum ada tagihan');
    }
}
