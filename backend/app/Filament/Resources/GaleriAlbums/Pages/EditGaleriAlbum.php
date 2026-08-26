<?php

namespace App\Filament\Resources\GaleriAlbums\Pages;

use App\Filament\Resources\GaleriAlbums\GaleriAlbumResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGaleriAlbum extends EditRecord
{
    protected static string $resource = GaleriAlbumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
