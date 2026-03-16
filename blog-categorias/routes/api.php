<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Puedes verificar las rutas creadas ejecutando php artisan route:list en tu consola.
Route::apiResource('posts', PostController::class);
Route::apiResource('categories', CategoryController::class);
