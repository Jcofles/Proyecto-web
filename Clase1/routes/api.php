<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NodoController;

/*
|--------------------------------------------------------------------------
| API routes - Nodos / Conexiones
|--------------------------------------------------------------------------
|
| Rutas limpias; Laravel 12 aplica CORS/global middleware desde la configuración.
|
*/
Route::get('nodos', [NodoController::class, 'index']);
Route::post('nodos', [NodoController::class, 'store']);
Route::post('nodos/conectar', [NodoController::class, 'conectar']);
