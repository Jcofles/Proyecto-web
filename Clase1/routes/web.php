<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Ruta para ver los breadcrumbs demo
Route::get('/breadcrumbs-demo', function () {
    return view('breadcrumbs-demo');
});
