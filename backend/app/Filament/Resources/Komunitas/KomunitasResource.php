<?php

namespace App\Filament\Resources\Komunitas;

use App\Filament\Resources\Komunitas\Pages\CreateKomunitas;
use App\Filament\Resources\Komunitas\Pages\EditKomunitas;
use App\Filament\Resources\Komunitas\Pages\ListKomunitas;
use App\Filament\Resources\Komunitas\Schemas\KomunitasForm;
use App\Filament\Resources\Komunitas\Tables\KomunitasTable;
use App\Models\Komunitas;
use App\Models\User;
use App\Services\RoleResolver;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KomunitasResource extends Resource
{
    protected static ?string $model = Komunitas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Komunitas';

    protected static \UnitEnum|string|null $navigationGroup = 'Anggota';

    public static function form(Schema $schema): Schema
    {
        return KomunitasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KomunitasTable::configure($table);
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
            'index' => ListKomunitas::route('/'),
            'create' => CreateKomunitas::route('/create'),
            'edit' => EditKomunitas::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return false;
        }
        $resolver = app(RoleResolver::class);

        return $resolver->isAdminPusat($user);
    }
}
