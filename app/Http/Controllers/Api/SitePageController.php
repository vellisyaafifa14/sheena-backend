<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SitePage;

class SitePageController extends Controller
{
    public function index()
    {
        $pages = SitePage::orderBy('id_page', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $pages
        ]);
    }

    public function showBySlug($slug)
    {
        $page = SitePage::with(['contentSections'])
            ->where('page_slug', $slug)
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page
        ]);
    }
}