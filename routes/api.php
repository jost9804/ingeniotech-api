<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\ProductController;

Route::post('/auth/login', [AuthController::class, 'login']);

// Catálogo público
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'publicShow']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::apiResource('jobs', JobController::class);

    // Gestión de productos (admin). POST para update por la subida de imágenes.
    Route::post('/products/generate-description', [ProductController::class, 'generateDescription']);
    Route::get('/admin/products', [ProductController::class, 'adminIndex']);
    Route::get('/admin/products/{product}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});
