<?php

namespace App\Filament\Resources\Komunitas\Pages;

use App\Filament\Resources\Komunitas\KomunitasResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKomunitas extends EditRecord
{
    protected static string $resource = KomunitasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
