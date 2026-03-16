<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\CategoryController;

Route::post('/admin/login', [AdminAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);

    Route::get('/admin/me', function (Request $request) {
        return response()->json([
            'message' => 'Data admin berhasil diambil',
            'data' => $request->user()
        ]);
    });
});

Route::get('/categories', [CategoryController::class, 'index']);