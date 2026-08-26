<?php

namespace App\Filament\Resources\Surats\Pages;

use App\Filament\Resources\Surats\SuratResource;
use App\Models\SuratStatusLog;
use App\Models\SuratTemplate;
use App\Services\PenomoranSurat;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateSurat extends CreateRecord
{
    protected static string $resource = SuratResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['jenis'] ?? '') === 'keluar') {
            $data = DB::transaction(function () use ($data) {
                $template = SuratTemplate::query()
                    ->where('id', $data['surat_template_id'] ?? 0)
                    ->where('periode_id', $data['periode_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $data['nomor_surat'] = app(PenomoranSurat::class)->nomorBerikutnya($template, $data['tanggal_surat']);
                $data['status'] = 'draft';

                return $data;
            });
        } else {
            unset($data['surat_template_id'], $data['nomor_surat']);
            $data['status'] = null;
        }

        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->jenis === 'keluar') {
            SuratStatusLog::query()->create([
                'surat_id' => $this->record->id,
                'status' => 'draft',
                'user_id' => auth()->id(),
            ]);
        }
    }
}
