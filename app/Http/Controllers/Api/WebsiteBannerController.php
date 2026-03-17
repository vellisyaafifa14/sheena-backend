<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebsiteBanner;

class WebsiteBannerController extends Controller
{
    public function index()
    {
        $banners = WebsiteBanner::orderBy('sort_order', 'asc')
            ->orderBy('id_banner', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $banners
        ]);
    }
}