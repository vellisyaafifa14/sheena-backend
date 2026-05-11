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
use App\Http\Controllers\Api\ShopeeAuthController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\HomeContentController;

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

Route::get('/admin/dashboard/summary', [AdminDashboardController::class, 'summary']);
Route::get('/admin/sales', [AdminDashboardController::class, 'sales']);
Route::get('/admin/reports/sales', [AdminDashboardController::class, 'reportsSales']);
Route::get('/admin/dashboard/latest-orders', [AdminDashboardController::class, 'latestOrders']);
Route::get('/admin/dashboard/best-selling', [AdminDashboardController::class, 'bestSellingProducts']);

Route::get('/admin/home-content', [HomeContentController::class, 'index']);
Route::post('/admin/home-content', [HomeContentController::class, 'update']);

Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::get('/channels', [ChannelController::class, 'index']);

Route::get('/channel-connections', [ChannelConnectionController::class, 'index']);
Route::post('/channel-connections', [ChannelConnectionController::class, 'store']);

Route::get('/shopee/auth-url', [ShopeeAuthController::class, 'getAuthUrl']);
Route::get('/shopee/callback', [ShopeeAuthController::class, 'callback']);
Route::post('/sync/shopee/products', [ShopeeSyncController::class, 'syncProducts']);
Route::post('/sync/shopee/orders', [ShopeeSyncController::class, 'syncOrders']);

Route::get('/site-pages', [SitePageController::class, 'index']);
Route::get('/site-pages/{slug}', [SitePageController::class, 'showBySlug']);
Route::post('/site-pages', [SitePageController::class, 'store']);
Route::put('/site-pages/{id}', [SitePageController::class, 'update']);

Route::get('/contact-infos', [ContactInfoController::class, 'index']);
Route::post('/contact-infos', [ContactInfoController::class, 'store']);
Route::put('/contact-infos/{id}', [ContactInfoController::class, 'update']);
Route::delete('/contact-infos/{id}', [ContactInfoController::class, 'destroy']);

Route::get('/website-banners', [WebsiteBannerController::class, 'index']);
Route::post('/website-banners', [WebsiteBannerController::class, 'store']);
Route::put('/website-banners/{id}', [WebsiteBannerController::class, 'update']);
Route::delete('/website-banners/{id}', [WebsiteBannerController::class, 'destroy']);

Route::get('/content-sections', [ContentSectionController::class, 'index']);
Route::post('/content-sections', [ContentSectionController::class, 'store']);
Route::put('/content-sections/{id}', [ContentSectionController::class, 'update']);
Route::delete('/content-sections/{id}', [ContentSectionController::class, 'destroy']);

