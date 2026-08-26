<?php

namespace App\Filament\Resources\GaleriAlbums;

use App\Filament\Resources\GaleriAlbums\Pages\CreateGaleriAlbum;
use App\Filament\Resources\GaleriAlbums\Pages\EditGaleriAlbum;
use App\Filament\Resources\GaleriAlbums\Pages\ListGaleriAlbums;
use App\Filament\Resources\GaleriAlbums\Schemas\GaleriAlbumForm;
use App\Filament\Resources\GaleriAlbums\Tables\GaleriAlbumsTable;
use App\Models\GaleriAlbum;
use App\Models\User;
use App\Services\RoleResolver;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GaleriAlbumResource extends Resource
{
    protected static ?string $model = GaleriAlbum::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Album Galeri';

    protected static \UnitEnum|string|null $navigationGroup = 'Konten Publik';

    public static function form(Schema $schema): Schema
    {
        return GaleriAlbumForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GaleriAlbumsTable::configure($table);
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
            'index' => ListGaleriAlbums::route('/'),
            'create' => CreateGaleriAlbum::route('/create'),
            'edit' => EditGaleriAlbum::route('/{record}/edit'),
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
