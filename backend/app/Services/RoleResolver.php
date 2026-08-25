<?php

namespace App\Services;

use App\Models\Penugasan;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Pemetaan otomatis role dari penugasan aktif
 * (docs/design/struktur-organisasi.md §4).
 *
 * Admin tidak perlu set role manual tiap pergantian pengurus —
 * cukup input penugasan baru, role ikut berubah.
 */
class RoleResolver
{
    /**
     * Kode komunitas yang dipegang user sebagai pengurus.
     *
     * @return Collection<int, string>
     */
    public function komunitasDikelola(User $user): Collection
    {
        return $this->penugasansAktif($user)
            ->filter(fn ($p) => $p->jabatan->tingkat === 'utama'
                && $p->jabatan->divisi->komunitas_id !== null)
            ->map(fn ($p) => $p->jabatan->divisi->komunitas->kode)
            ->unique()
            ->values();
    }

    public function isAdminPusat(User $user): bool
    {
        return $this->penugasansAktif($user)->contains(
            fn ($p) => in_array($p->jabatan->nama, ['Ketua Umum', 'Wakil Ketua Umum'], true)
        );
    }

    public function isBendahara(User $user): bool
    {
        return $this->penugasansAktif($user)->contains(
            fn ($p) => str_starts_with($p->jabatan->nama, 'Bendahara')
        );
    }

    public function isSekretaris(User $user): bool
    {
        return $this->penugasansAktif($user)->contains(
            fn ($p) => str_starts_with($p->jabatan->nama, 'Sekretaris')
        );
    }

    /**
     * Apakah user pengurus komunitas berkode $kode?
     */
    public function isPengurusKomunitas(User $user, string $kode): bool
    {
        if ($this->isAdminPusat($user)) {
            return true;
        }

        return $this->komunitasDikelola($user)->contains($kode);
    }

    /**
     * @return Collection<int, Penugasan>
     */
    private function penugasansAktif(User $user): Collection
    {
        return $user->member?->penugasans()
            ->whereHas('periode', fn ($q) => $q->where('status', 'aktif'))
            ->with(['jabatan.divisi.komunitas', 'periode'])
            ->get()
            ?? collect();
    }
}
