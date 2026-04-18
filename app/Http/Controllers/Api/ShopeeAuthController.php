<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShopeeAuthController extends Controller
{
    public function callback(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Shopee callback received',
            'query' => $request->query(),
        ]);
    }
}