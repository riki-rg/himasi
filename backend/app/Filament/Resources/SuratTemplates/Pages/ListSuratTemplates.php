<?php

namespace App\Filament\Resources\SuratTemplates\Pages;

use App\Filament\Resources\SuratTemplates\SuratTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuratTemplates extends ListRecords
{
    protected static string $resource = SuratTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
