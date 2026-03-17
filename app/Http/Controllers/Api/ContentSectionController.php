<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentSection;
use Illuminate\Http\Request;

class ContentSectionController extends Controller
{
    public function index(Request $request)
    {
        $query = ContentSection::with('sitePage')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id_section', 'asc');

        if ($request->has('id_page')) {
            $query->where('id_page', $request->id_page);
        }

        $sections = $query->get();

        return response()->json([
            'success' => true,
            'data' => $sections
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_page' => 'required|exists:site_pages,id_page',
            'section_name' => 'required|string|max:100',
            'section_key' => 'nullable|string|max:255',
            'section_content' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $section = ContentSection::create([
            'id_page' => $validated['id_page'],
            'section_name' => $validated['section_name'],
            'section_key' => $validated['section_key'] ?? null,
            'section_content' => $validated['section_content'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Content section created successfully',
            'data' => $section
        ], 201);
    }
}