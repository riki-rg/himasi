<?php

namespace App\Filament\Resources\Rapats\Pages;

use App\Filament\Resources\Rapats\RapatResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRapat extends CreateRecord
{
    protected static string $resource = RapatResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['qr_secret'] = bin2hex(random_bytes(20));

        return $data;
    }
}
