<?php

namespace App\Filament\Resources\KasKategoris;

use App\Filament\Resources\KasKategoris\Pages\CreateKasKategori;
use App\Filament\Resources\KasKategoris\Pages\EditKasKategori;
use App\Filament\Resources\KasKategoris\Pages\ListKasKategoris;
use App\Filament\Resources\KasKategoris\Schemas\KasKategoriForm;
use App\Filament\Resources\KasKategoris\Tables\KasKategorisTable;
use App\Models\KasKategori;
use App\Models\User;
use App\Services\RoleResolver;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KasKategoriResource extends Resource
{
    protected static ?string $model = KasKategori::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Kategori Kas';

    protected static \UnitEnum|string|null $navigationGroup = 'Keuangan';

    public static function form(Schema $schema): Schema
    {
        return KasKategoriForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KasKategorisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKasKategoris::route('/'),
            'create' => CreateKasKategori::route('/create'),
            'edit' => EditKasKategori::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return false;
        }
        $resolver = app(RoleResolver::class);

        return $resolver->isBendahara($user) || $resolver->isAdminPusat($user);
    }
}
