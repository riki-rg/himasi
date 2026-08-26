<?php

namespace App\Filament\Resources\SuratTemplates\Pages;

use App\Filament\Resources\SuratTemplates\SuratTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSuratTemplate extends EditRecord
{
    protected static string $resource = SuratTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
