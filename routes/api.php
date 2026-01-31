<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\PenjualanController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Resource routes (CRUD)
    Route::apiResource('kategoris', KategoriController::class);
    Route::apiResource('barangs', BarangController::class);
    Route::apiResource('pelanggans', PelangganController::class);
    Route::apiResource('penjualans', PenjualanController::class)->only(['index', 'show', 'store']);

    // Custom routes
    Route::get('/penjualans/summary', [PenjualanController::class, 'summary']);
    Route::get('/audit-logs', [\App\Http\Controllers\Api\AuditLogController::class, 'index']);

    // Analytics routes
    Route::get('/analytics/summary', [AnalyticsController::class, 'summary']);
    Route::get('/analytics/top-kategori', [AnalyticsController::class, 'topKategori']);
    Route::get('/analytics/kasir-performance', [AnalyticsController::class, 'kasirPerformance']);
});
