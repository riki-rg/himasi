<?php

namespace App\Filament\Resources\Rapats;

use App\Filament\Resources\Rapats\Pages\CreateRapat;
use App\Filament\Resources\Rapats\Pages\EditRapat;
use App\Filament\Resources\Rapats\Pages\ListRapats;
use App\Filament\Resources\Rapats\Schemas\RapatForm;
use App\Filament\Resources\Rapats\Tables\RapatsTable;
use App\Models\Rapat;
use App\Models\User;
use App\Services\RoleResolver;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RapatResource extends Resource
{
    protected static ?string $model = Rapat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Rapat';

    protected static \UnitEnum|string|null $navigationGroup = 'Administrasi';

    public static function form(Schema $schema): Schema
    {
        return RapatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RapatsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PesertaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRapats::route('/'),
            'create' => CreateRapat::route('/create'),
            'edit' => EditRapat::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return false;
        }
        $resolver = app(RoleResolver::class);

        return $resolver->isAdminPusat($user) || $resolver->komunitasDikelola($user)->isNotEmpty();
    }
}
