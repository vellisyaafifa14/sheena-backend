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
            'data' => HomeContent::pluck('value', 'key'),
        ]);
    }

    public function update(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            HomeContent::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Home content updated successfully',
        ]);
    }
}