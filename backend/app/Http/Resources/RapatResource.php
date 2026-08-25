<?php

namespace App\Http\Resources;

use App\Models\Rapat;
use App\Services\QrPresensi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Rapat */
class RapatResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'tanggal' => $this->tanggal?->toDateString(),
            'jam_mulai' => substr((string) $this->jam_mulai, 0, 5),
            'jam_selesai' => $this->jam_selesai !== null ? substr((string) $this->jam_selesai, 0, 5) : null,
            'tempat' => $this->tempat,
            'agenda' => $this->agenda,
            'notulen' => $this->notulen,
            'lampiran_path' => $this->lampiran_path,
            'komunitas_id' => $this->komunitas_id,
            'status' => $this->status,
            'window_aktif' => app(QrPresensi::class)->jendelaAktif($this->resource),
            'peserta' => $this->whenLoaded('peserta', fn () => $this->peserta
                ->map(fn ($p) => [
                    'member' => $p->member ? [
                        'nim' => $p->member->nim,
                        'nama' => $p->member->nama,
                    ] : null,
                    'kehadiran' => $p->kehadiran,
                    'waktu_absen' => $p->waktu_absen?->toISOString(),
                    'catatan' => $p->catatan,
                ])->values()),
        ];
    }
}
