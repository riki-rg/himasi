<?php

namespace App\Filament\Resources\Penugasans;

use App\Filament\Resources\Penugasans\Pages\CreatePenugasan;
use App\Filament\Resources\Penugasans\Pages\EditPenugasan;
use App\Filament\Resources\Penugasans\Pages\ListPenugasans;
use App\Filament\Resources\Penugasans\Schemas\PenugasanForm;
use App\Filament\Resources\Penugasans\Tables\PenugasansTable;
use App\Models\Penugasan;
use App\Models\User;
use App\Services\RoleResolver;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PenugasanResource extends Resource
{
    protected static ?string $model = Penugasan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Penugasan';

    protected static \UnitEnum|string|null $navigationGroup = 'Struktur Organisasi';

    public static function form(Schema $schema): Schema
    {
        return PenugasanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PenugasansTable::configure($table);
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
            'index' => ListPenugasans::route('/'),
            'create' => CreatePenugasan::route('/create'),
            'edit' => EditPenugasan::route('/{record}/edit'),
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
