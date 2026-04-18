<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShopeeService;
use Illuminate\Http\Request;

class ShopeeAuthController extends Controller
{
    protected ShopeeService $shopeeService;

    public function __construct(ShopeeService $shopeeService)
    {
        $this->shopeeService = $shopeeService;
    }

    public function getAuthUrl()
    {
        $result = $this->shopeeService->getAuthUrl();
        return response()->json($result);
    }

    public function callback(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Shopee callback received',
            'query' => $request->query(),
        ]);
    }
}