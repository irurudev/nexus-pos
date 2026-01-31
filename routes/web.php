<?php

use App\Http\Controllers\DocumentationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/documentation', function () {
    return view('swagger-ui');
});

Route::get('/api/swagger.json', [DocumentationController::class, 'swagger']);
