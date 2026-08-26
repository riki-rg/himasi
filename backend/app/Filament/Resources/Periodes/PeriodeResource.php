<?php

namespace App\Filament\Resources\Periodes;

use App\Filament\Resources\Periodes\Pages\CreatePeriode;
use App\Filament\Resources\Periodes\Pages\EditPeriode;
use App\Filament\Resources\Periodes\Pages\ListPeriodes;
use App\Filament\Resources\Periodes\Schemas\PeriodeForm;
use App\Filament\Resources\Periodes\Tables\PeriodesTable;
use App\Models\Periode;
use App\Models\User;
use App\Services\RoleResolver;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PeriodeResource extends Resource
{
    protected static ?string $model = Periode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Periode';

    protected static \UnitEnum|string|null $navigationGroup = 'Struktur Organisasi';

    public static function form(Schema $schema): Schema
    {
        return PeriodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PeriodesTable::configure($table);
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
            'index' => ListPeriodes::route('/'),
            'create' => CreatePeriode::route('/create'),
            'edit' => EditPeriode::route('/{record}/edit'),
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
