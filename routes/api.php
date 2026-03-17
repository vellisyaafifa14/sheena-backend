<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\ChannelConnectionController;
use App\Http\Controllers\Api\ShopeeSyncController;
use App\Http\Controllers\Api\SitePageController;
use App\Http\Controllers\Api\ContactInfoController;
use App\Http\Controllers\Api\WebsiteBannerController;
use App\Http\Controllers\Api\ContentSectionController;

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

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::get('/channels', [ChannelController::class, 'index']);

Route::get('/channel-connections', [ChannelConnectionController::class, 'index']);
Route::post('/channel-connections', [ChannelConnectionController::class, 'store']);

Route::post('/sync/shopee/products', [ShopeeSyncController::class, 'syncProducts']);

Route::get('/site-pages', [SitePageController::class, 'index']);
Route::get('/site-pages/{slug}', [SitePageController::class, 'showBySlug']);
Route::post('/site-pages', [SitePageController::class, 'store']);
Route::put('/site-pages/{id}', [SitePageController::class, 'update']);

Route::get('/contact-infos', [ContactInfoController::class, 'index']);
Route::post('/contact-infos', [ContactInfoController::class, 'store']);
Route::put('/contact-infos/{id}', [ContactInfoController::class, 'update']);

Route::get('/website-banners', [WebsiteBannerController::class, 'index']);
Route::post('/website-banners', [WebsiteBannerController::class, 'store']);

Route::get('/content-sections', [ContentSectionController::class, 'index']);
Route::post('/content-sections', [ContentSectionController::class, 'store']);
Route::put('/content-sections/{id}', [ContentSectionController::class, 'update']);