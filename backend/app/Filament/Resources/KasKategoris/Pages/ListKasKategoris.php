<?php

namespace App\Filament\Resources\KasKategoris\Pages;

use App\Filament\Resources\KasKategoris\KasKategoriResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKasKategoris extends ListRecords
{
    protected static string $resource = KasKategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
