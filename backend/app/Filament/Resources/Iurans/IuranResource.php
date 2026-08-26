<?php

namespace App\Filament\Resources\Iurans;

use App\Filament\Resources\Iurans\Pages\CreateIuran;
use App\Filament\Resources\Iurans\Pages\EditIuran;
use App\Filament\Resources\Iurans\Pages\ListIurans;
use App\Filament\Resources\Iurans\Schemas\IuranForm;
use App\Filament\Resources\Iurans\Tables\IuransTable;
use App\Models\Iuran;
use App\Models\User;
use App\Services\RoleResolver;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IuranResource extends Resource
{
    protected static ?string $model = Iuran::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Iuran';

    protected static \UnitEnum|string|null $navigationGroup = 'Keuangan';

    public static function form(Schema $schema): Schema
    {
        return IuranForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IuransTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TagihansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIurans::route('/'),
            'create' => CreateIuran::route('/create'),
            'edit' => EditIuran::route('/{record}/edit'),
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
