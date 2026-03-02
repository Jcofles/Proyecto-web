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

// Recuperación de contraseña
Route::post('auth/forgot-password', [PasswordResetController::class, 'sendCode']);
Route::post('auth/verify-code', [PasswordResetController::class, 'verifyCode']);
Route::post('auth/reset-password', [PasswordResetController::class, 'resetPassword']);

// Nodos
Route::get('nodos', [NodoController::class, 'index']);
Route::post('nodos', [NodoController::class, 'store']);
Route::post('nodos/conectar', [NodoController::class, 'conectar']);
