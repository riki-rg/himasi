<?php

namespace App\Filament\Resources\Komunitas\Pages;

use App\Filament\Resources\Komunitas\KomunitasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKomunitas extends ListRecords
{
    protected static string $resource = KomunitasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
