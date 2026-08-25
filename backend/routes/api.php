<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\KeanggotaanController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PublikController;
use App\Http\Controllers\StrukturController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::patch('/me', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'updatePassword']);
    });
});

Route::prefix('publik')->group(function (): void {
    Route::get('/struktur', [StrukturController::class, 'strukturPublik']);
    Route::get('/artikels', [PublikController::class, 'artikels']);
    Route::get('/artikels/{slug}', [PublikController::class, 'artikelDetail']);
    Route::get('/events', [PublikController::class, 'events']);
    Route::get('/events/{id}', [PublikController::class, 'eventDetail']);
    Route::get('/galeri/albums', [PublikController::class, 'albums']);
    Route::get('/galeri/albums/{album}', [PublikController::class, 'albumDetail']);
    Route::get('/pengumumans', [PublikController::class, 'pengumumans']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/keanggotaan', [KeanggotaanController::class, 'store']);
    Route::patch('/keanggotaan/{keanggotaan}', [KeanggotaanController::class, 'update']);
    Route::delete('/keanggotaan/{keanggotaan}', [KeanggotaanController::class, 'destroy']);

    Route::middleware('can:kelola-anggota')->group(function (): void {
        Route::get('/anggota/export', [AnggotaController::class, 'export']);
        Route::post('/anggota/import', [AnggotaController::class, 'import']);
        Route::apiResource('anggota', AnggotaController::class)
            ->parameters(['anggota' => 'anggota'])
            ->only(['index', 'store', 'show', 'update', 'destroy']);
    });

    Route::middleware('can:kelola-struktur')->prefix('periodes')->group(function (): void {
        Route::get('/', [StrukturController::class, 'periodes']);
        Route::post('/', [StrukturController::class, 'storePeriode']);
        Route::post('/{periode}/arsipkan', [StrukturController::class, 'arsipkan']);
        Route::get('/{periode}/divisi', [StrukturController::class, 'divisis']);
        Route::post('/{periode}/divisi', [StrukturController::class, 'storeDivisi']);
    });

    Route::middleware('can:kelola-struktur')->group(function (): void {
        Route::get('/divisi/{divisi}/jabatan', [StrukturController::class, 'jabatans']);
        Route::post('/divisi/{divisi}/jabatan', [StrukturController::class, 'storeJabatan']);
        Route::post('/penugasan', [StrukturController::class, 'storePenugasan']);
        Route::delete('/penugasan/{penugasan}', [StrukturController::class, 'destroyPenugasan']);
    });

    Route::middleware('can:kelola-konten')->group(function (): void {
        Route::apiResource('artikels', ArtikelController::class);
        Route::apiResource('events', EventController::class);

        Route::get('/galeri/albums', [GaleriController::class, 'albums']);
        Route::post('/galeri/albums', [GaleriController::class, 'storeAlbum']);
        Route::put('/galeri/albums/{album}', [GaleriController::class, 'updateAlbum'])
            ->scopeBindings();
        Route::delete('/galeri/albums/{album}', [GaleriController::class, 'destroyAlbum'])
            ->scopeBindings();
        Route::post('/galeri/albums/{album}/fotos', [GaleriController::class, 'storeFotos'])
            ->scopeBindings();
        Route::put('/galeri/fotos/{foto}', [GaleriController::class, 'updateFoto']);
        Route::delete('/galeri/fotos/{foto}', [GaleriController::class, 'destroyFoto']);

        Route::get('/pengumumans', [PengumumanController::class, 'index']);
        Route::post('/pengumumans', [PengumumanController::class, 'store']);
        Route::put('/pengumumans/{pengumuman}', [PengumumanController::class, 'update']);
        Route::delete('/pengumumans/{pengumuman}', [PengumumanController::class, 'destroy']);
    });
});
