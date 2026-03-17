<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactInfo;
use Illuminate\Http\Request;

class ContactInfoController extends Controller
{
    public function index()
    {
        $contactInfos = ContactInfo::orderBy('id_contact', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $contactInfos
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_type' => 'required|string|max:50',
            'contact_label' => 'nullable|string|max:100',
            'contact_value' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $contactInfo = ContactInfo::create([
            'contact_type' => $validated['contact_type'],
            'contact_label' => $validated['contact_label'] ?? null,
            'contact_value' => $validated['contact_value'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contact info created successfully',
            'data' => $contactInfo
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $contactInfo = ContactInfo::find($id);

        if (!$contactInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Contact info not found'
            ], 404);
        }

        $validated = $request->validate([
            'contact_type' => 'required|string|max:50',
            'contact_label' => 'required|string|max:100',
            'contact_value' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $contactInfo->update([
            'contact_type' => $validated['contact_type'],
            'contact_label' => $validated['contact_label'] ?? null,
            'contact_value' => $validated['contact_value'],
            'is_active' => $validated['is_active'] ?? $contactInfo->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contact info updated successfully',
            'data' => $contactInfo
        ]);
    }
}