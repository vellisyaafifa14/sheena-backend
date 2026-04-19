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

    public function callback(Request $request)
    {
        $code = $request->query('code');
        $shopId = $request->query('shop_id');

        if (!function_exists('curl_init')) {
        return response()->json([
            'success' => false,
            'message' => 'cURL not available on server',
        ], 500);
    }

        if (!$code || !$shopId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing code or shop_id',
                'query' => $request->query(),
            ], 400);
        }

        $tokenResult = $this->shopeeService->getAccessToken($code, $shopId);

        return response()->json([
            'success' => true,
            'message' => 'Shopee callback received',
            'query' => $request->query(),
            'token_result' => $tokenResult,
        ]);
    }
}