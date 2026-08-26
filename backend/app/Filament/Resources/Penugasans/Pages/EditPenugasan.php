<?php

namespace App\Filament\Resources\Penugasans\Pages;

use App\Filament\Resources\Penugasans\PenugasanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPenugasan extends EditRecord
{
    protected static string $resource = PenugasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
