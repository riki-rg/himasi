<?php

namespace App\Filament\Resources\KasKategoris\Pages;

use App\Filament\Resources\KasKategoris\KasKategoriResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKasKategori extends EditRecord
{
    protected static string $resource = KasKategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
