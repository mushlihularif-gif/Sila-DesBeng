<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BerandaController;

// Public Routes (Bisa diakses tanpa login)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/google', [AuthController::class, 'loginGoogle']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/banners', [BerandaController::class, 'banners']);
Route::get('/services', [BerandaController::class, 'services']);
Route::get('/unit-pelayanan', [BerandaController::class, 'unitPelayanan']);
Route::get('/announcements', [BerandaController::class, 'announcements']);

// Protected Routes (Harus mengirimkan Bearer Token dari hasil Login)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/profile/password', [AuthController::class, 'updatePassword']);
    
    // Laporan
    Route::post('/laporan', [\App\Http\Controllers\Api\LaporanController::class, 'store']);
    Route::get('/admin-reports', [\App\Http\Controllers\Api\LaporanController::class, 'getAdminReports']);
    Route::post('/admin-reports/{id}/forward', [\App\Http\Controllers\Api\LaporanController::class, 'forwardReport']);
    
    // Gas
    Route::get('/gas', [\App\Http\Controllers\Api\GasController::class, 'index']);
    Route::post('/gas/booking', [\App\Http\Controllers\Api\GasBookingController::class, 'store']);
    
    // Notifikasi
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/delete-all', [\App\Http\Controllers\Api\NotificationController::class, 'deleteAll']);
    
    Route::post('/logout', [AuthController::class, 'logout']);
});
