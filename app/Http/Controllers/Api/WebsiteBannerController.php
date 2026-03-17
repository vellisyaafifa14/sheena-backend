<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebsiteBanner;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'banner_title' => 'nullable|string|max:150',
            'banner_image' => 'required|string|max:255',
            'banner_link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $banner = WebsiteBanner::create([
            'banner_title' => $validated['banner_title'] ?? null,
            'banner_image' => $validated['banner_image'],
            'banner_link' => $validated['banner_link'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Website banner created successfully',
            'data' => $banner
        ], 201);
    }
}