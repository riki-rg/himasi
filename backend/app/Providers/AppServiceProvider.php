<?php

namespace App\Providers;

use App\Models\User;
use App\Services\RoleResolver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Gate::define('admin-pusat', fn (User $user) => app(RoleResolver::class)->isAdminPusat($user));
        Gate::define('bendahara', fn (User $user) => app(RoleResolver::class)->isBendahara($user));
        Gate::define('sekretaris', fn (User $user) => app(RoleResolver::class)->isSekretaris($user));
        Gate::define('pengurus-komunitas', function (User $user, string $kode) {
            return app(RoleResolver::class)->isPengurusKomunitas($user, $kode);
        });
        Gate::define('kelola-anggota', function (User $user) {
            if (app(RoleResolver::class)->isAdminPusat($user)) {
                return true;
            }

            return $user->member?->penugasans()
                ->whereHas('periode', fn ($q) => $q->where('status', 'aktif'))
                ->whereHas('jabatan', fn ($q) => $q->where('tingkat', 'utama'))
                ->exists() ?? false;
        });
        Gate::define('kelola-struktur', fn (User $user) => app(RoleResolver::class)->isAdminPusat($user));
        Gate::define('kelola-surat', fn (User $user) => app(RoleResolver::class)->isAdminPusat($user)
            || app(RoleResolver::class)->isSekretaris($user));
        Gate::define('kelola-konten', function (User $user) {
            if (app(RoleResolver::class)->isAdminPusat($user)) {
                return true;
            }

            return $user->member?->penugasans()
                ->whereHas('periode', fn ($q) => $q->where('status', 'aktif'))
                ->whereHas('jabatan', fn ($q) => $q->whereIn('tingkat', ['utama', 'staf']))
                ->exists() ?? false;
        });
    }
}
