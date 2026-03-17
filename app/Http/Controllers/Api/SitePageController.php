<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'page_name' => 'required|string|max:100',
            'page_slug' => 'required|string|max:255|unique:site_pages,page_slug',
            'is_active' => 'nullable|boolean',
        ]);

        $page = SitePage::create([
            'page_name' => $validated['page_name'],
            'page_slug' => $validated['page_slug'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Site page created successfully',
            'data' => $page
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $page = SitePage::find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        $validated = $request->validate([
            'page_name' => 'required|string|max:100',
            'page_slug' => 'required|string|max:255|unique:site_pages,page_slug,' . $id . ',id_page',
            'is_active' => 'nullable|boolean',
        ]);

        $page->update([
            'page_name' => $validated['page_name'],
            'page_slug' => $validated['page_slug'],
            'is_active' => $validated['is_active'] ?? $page->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Site page updated successfully',
            'data' => $page
        ]);
    }
}