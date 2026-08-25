<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KeanggotaanController;
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

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/keanggotaan', [KeanggotaanController::class, 'store']);
    Route::patch('/keanggotaan/{keanggotaan}', [KeanggotaanController::class, 'update']);
    Route::delete('/keanggotaan/{keanggotaan}', [KeanggotaanController::class, 'destroy']);

    Route::middleware('can:kelola-anggota')->group(function (): void {
        Route::get('/anggota', [AnggotaController::class, 'index']);
        Route::post('/anggota', [AnggotaController::class, 'store']);
        Route::get('/anggota/export', [AnggotaController::class, 'export']);
        Route::post('/anggota/import', [AnggotaController::class, 'import']);
        Route::get('/anggota/{anggota}', [AnggotaController::class, 'show']);
        Route::put('/anggota/{anggota}', [AnggotaController::class, 'update']);
        Route::delete('/anggota/{anggota}', [AnggotaController::class, 'destroy']);
    });
});
