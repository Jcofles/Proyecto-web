<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NodoController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\LoginController;

/*
|--------------------------------------------------------------------------
| API routes - Auth / Nodos / Conexiones
|--------------------------------------------------------------------------
|
| Rutas limpias; Laravel 12 aplica CORS/global middleware desde la configuración.
|
*/

// Autenticación
Route::post('auth/login', [LoginController::class, 'login']);
Route::post('auth/register', [RegisterController::class, 'register']);
Route::post('auth/verify-email', [RegisterController::class, 'verifyEmail']);
Route::post('auth/resend-verification', [RegisterController::class, 'resendVerification']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [LoginController::class, 'logout']);
    Route::get('auth/user', [LoginController::class, 'user']);
    Route::put('auth/update-profile', [LoginController::class, 'updateProfile']);
    Route::delete('auth/delete-account', [LoginController::class, 'deleteAccount']);
});

// Recuperación de contraseña
Route::post('auth/forgot-password', [PasswordResetController::class, 'sendCode']);
Route::post('auth/verify-code', [PasswordResetController::class, 'verifyCode']);
Route::post('auth/reset-password', [PasswordResetController::class, 'resetPassword']);

// Admin - Bloquear/Desbloquear usuarios (proteger con middleware admin en producción)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('admin/users/{userId}/block', [\App\Http\Controllers\Api\AdminController::class, 'blockUser']);
    Route::post('admin/users/{userId}/unblock', [\App\Http\Controllers\Api\AdminController::class, 'unblockUser']);
});

// Nodos
Route::get('nodos', [NodoController::class, 'index']);
Route::post('nodos', [NodoController::class, 'store']);
Route::post('nodos/conectar', [NodoController::class, 'conectar']);
