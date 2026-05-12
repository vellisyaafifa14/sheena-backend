<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeContent;
use Illuminate\Http\Request;

class HomeContentController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => HomeContent::pluck('value', 'key')->toArray(),
        ]);
    }

    public function update(Request $request)
    {
        foreach ($request->except([
            'hero_image',
            'section2_image_1',
            'section2_image_2',
            'section2_image_3',
            'section2_image_4',
            'promo_image',
        ]) as $key => $value) {
            HomeContent::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $imageFields = [
            'hero_image',
            'section2_image_1',
            'section2_image_2',
            'section2_image_3',
            'section2_image_4',
            'promo_image',
        ];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('home-content', 'public');

                HomeContent::updateOrCreate(
                    ['key' => $field],
                    ['value' => '/storage/' . $path]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Home content updated successfully',
        ]);
    }
}