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

    $authUrl = $result['auth_url'] ?? $result['data']['auth_url'] ?? null;

    if (!$authUrl) {
        return response()->json($result, 500);
    }

    return redirect()->away($authUrl);
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

    // save token ke database jika berhasil)
    if ($tokenResult['success'] && empty($tokenResult['response']['error'])) {
        $tokenData = $tokenResult['response'];

        // disconnect koneksi lama
        \App\Models\ChannelConnection::where('id_channel', 1)
            ->where('shop_id', '!=', (string) $shopId)
            ->update(['status_connection' => 'disconnected']);

        // simpan/update koneksi baru
        \App\Models\ChannelConnection::updateOrCreate(
            [
                'id_channel' => 1,
                'shop_id' => (string) $shopId,
            ],
            [
                'shop_name' => 'Sheena',
                'access_token' => $tokenData['access_token'] ?? null,
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'status_connection' => 'connected',
            ]
        );
    }

    return response()->json([
        'success' => true,
        'message' => 'Shopee callback received',
        'query' => $request->query(),
        'token_result' => $tokenResult,
    ]);
}
}