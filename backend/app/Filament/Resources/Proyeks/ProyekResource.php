<?php

namespace App\Filament\Resources\Proyeks;

use App\Filament\Resources\Proyeks\Pages\CreateProyek;
use App\Filament\Resources\Proyeks\Pages\EditProyek;
use App\Filament\Resources\Proyeks\Pages\ListProyeks;
use App\Filament\Resources\Proyeks\Schemas\ProyekForm;
use App\Filament\Resources\Proyeks\Tables\ProyeksTable;
use App\Models\Proyek;
use App\Models\User;
use App\Services\RoleResolver;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProyekResource extends Resource
{
    protected static ?string $model = Proyek::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRocketLaunch;

    protected static ?string $navigationLabel = 'Karya';

    protected static \UnitEnum|string|null $navigationGroup = 'Konten Publik';

    public static function form(Schema $schema): Schema
    {
        return ProyekForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProyeksTable::configure($table);
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
            'index' => ListProyeks::route('/'),
            'create' => CreateProyek::route('/create'),
            'edit' => EditProyek::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return false;
        }
        $resolver = app(RoleResolver::class);

        return $resolver->isAdminPusat($user) || $user->member?->penugasans()->whereHas('periode', fn ($q) => $q->where('status', 'aktif'))->exists();
    }
}
