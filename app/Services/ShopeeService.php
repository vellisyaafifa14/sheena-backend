<?php

namespace App\Services;

class ShopeeService
{
    public function getAuthUrl(): array
    {
        $partnerId = trim((string) env('SHOPEE_PARTNER_ID'));
        $partnerKey = trim((string) env('SHOPEE_PARTNER_KEY'));
        $redirectUrl = trim((string) env('SHOPEE_REDIRECT_URL'));
        $baseUrl = rtrim((string) env('SHOPEE_AUTH_BASE_URL'), '/');

        $timestamp = time();
        $path = '/api/v2/shop/auth_partner';

        $baseString = $partnerId . $path . $timestamp;
        $sign = hash_hmac('sha256', $baseString, $partnerKey);

        $authUrl = $baseUrl . $path
            . '?partner_id=' . $partnerId
            . '&timestamp=' . $timestamp
            . '&sign=' . $sign
            . '&redirect=' . urlencode($redirectUrl);

        return [
            'success' => true,
            'message' => 'Shopee auth URL generated successfully',
            'data' => [
                'auth_url' => $authUrl,
                'timestamp' => $timestamp,
                'path' => $path,
                'base_string' => $baseString,
                'sign' => $sign,
                'partner_id' => $partnerId,
                'partner_key_preview' => substr($partnerKey, 0, 6),
                'partner_key_length' => strlen($partnerKey),
                'base_url' => $baseUrl,
                'redirect_url' => $redirectUrl,
            ]
        ];
    }

    public function getAccessToken(string $code, string $shopId): array
    {
        $partnerId = trim((string) env('SHOPEE_PARTNER_ID'));
        $partnerKey = trim((string) env('SHOPEE_PARTNER_KEY'));
        $baseUrl = rtrim((string) env('SHOPEE_API_BASE_URL'), '/');

        $code = trim($code);
        $shopId = (int) trim($shopId);
        #dd(env('SHOPEE_API_BASE_URL'));

        $timestamp = time();
        $path = '/api/v2/auth/token/get';

        $baseString = $partnerId . $path . $timestamp;
        $sign = hash_hmac('sha256', $baseString, $partnerKey);

        $url = $baseUrl . $path;

        $payload = [
            'code' => $code,
            'shop_id' => $shopId,
            'partner_id' => (int) $partnerId,
        ];

        $headers = [
            'Content-Type: application/json',
        ];

        $query = http_build_query([
            'partner_id' => $partnerId,
            'timestamp' => $timestamp,
            'sign' => $sign,
        ]);

        $ch = curl_init($url . '?' . $query);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'curl_error' => $curlError,
            'response' => json_decode($response, true),
            'raw_response' => $response,
        ];
    }

    public function getProducts(): array
{
    $partnerId = trim((string) env('SHOPEE_PARTNER_ID'));
    $partnerKey = trim((string) env('SHOPEE_PARTNER_KEY'));
    $baseUrl = rtrim((string) env('SHOPEE_API_BASE_URL'), '/');

    $connection = \App\Models\ChannelConnection::where('id_channel', 1)
        ->where('status_connection', 'connected')
        ->latest('id_connection')
        ->first();

    if (!$connection) {
        return [
            'success' => false,
            'message' => 'Koneksi Shopee tidak ditemukan',
        ];
    }

    $accessToken = $connection->access_token;
    $shopId = (int) $connection->shop_id;

    $timestamp = time();
    $path = '/api/v2/product/get_item_list';

    $allItems = [];
    $offset = 0;
    $pageSize = 20;
    $hasNextPage = true;
    $lastResponse = null;
    $lastCurlError = '';
    $lastHttpCode = 0;

    while ($hasNextPage) {
        $timestamp = time();
        $baseString = $partnerId . $path . $timestamp . $accessToken . $shopId;
        $sign = hash_hmac('sha256', $baseString, $partnerKey);

        $query = http_build_query([
            'partner_id' => $partnerId,
            'timestamp' => $timestamp,
            'access_token' => $accessToken,
            'shop_id' => $shopId,
            'sign' => $sign,
            'offset' => $offset,
            'page_size' => $pageSize,
            'item_status' => 'NORMAL',
        ]);

        $url = $baseUrl . $path . '?' . $query;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $decoded = json_decode($response, true);

        $lastResponse = $decoded;
        $lastCurlError = $curlError;
        $lastHttpCode = $httpCode;

        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'success' => false,
                'http_code' => $httpCode,
                'curl_error' => $curlError,
                'response' => $decoded,
                'raw_response' => $response,
                'debug' => [
                    'shop_id' => $shopId,
                    'path' => $path,
                    'offset' => $offset,
                ],
            ];
        }

        $items = $decoded['response']['item'] ?? [];
        $allItems = array_merge($allItems, $items);

        $hasNextPage = $decoded['response']['has_next_page'] ?? false;
        $offset = $decoded['response']['next'] ?? '';

        if ($offset === '' || $offset === null) {
            $hasNextPage = false;
        }
    }

    return [
        'success' => true,
        'http_code' => $lastHttpCode,
        'curl_error' => $lastCurlError,
        'response' => [
            'error' => '',
            'message' => '',
            'response' => [
                'item' => $allItems,
                'total_count' => count($allItems),
                'has_next_page' => false,
                'next' => '',
            ],
        ],
        'raw_response' => $lastResponse,
        'debug' => [
            'shop_id' => $shopId,
            'path' => $path,
            'total_fetched' => count($allItems),
        ],
    ];
}

    public function getProductDetail(int $itemId): array
{
    $partnerId = trim((string) env('SHOPEE_PARTNER_ID'));
    $partnerKey = trim((string) env('SHOPEE_PARTNER_KEY'));
    $baseUrl = rtrim((string) env('SHOPEE_API_BASE_URL'), '/');

    $connection = \App\Models\ChannelConnection::where('id_channel', 1)
        ->where('status_connection', 'connected')
        ->latest('id_connection')
        ->first();

    if (!$connection) {
        return [
            'success' => false,
            'message' => 'Koneksi Shopee tidak ditemukan',
        ];
    }

    $accessToken = $connection->access_token;
    $shopId = (int) $connection->shop_id;

    $timestamp = time();
    $path = '/api/v2/product/get_item_base_info';

    $baseString = $partnerId . $path . $timestamp . $accessToken . $shopId;
    $sign = hash_hmac('sha256', $baseString, $partnerKey);

    $query = http_build_query([
        'partner_id' => $partnerId,
        'timestamp' => $timestamp,
        'access_token' => $accessToken,
        'shop_id' => $shopId,
        'sign' => $sign,
        'item_id_list' => $itemId,
    ]);

    $url = $baseUrl . $path . '?' . $query;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'response' => json_decode($response, true),
        'raw_response' => $response,
        'debug' => [
            'shop_id' => $shopId,
            'item_id' => $itemId,
            'path' => $path,
        ],
    ];
}

public function getOrders(): array
{
    $partnerId = trim((string) env('SHOPEE_PARTNER_ID'));
    $partnerKey = trim((string) env('SHOPEE_PARTNER_KEY'));
    $baseUrl = rtrim((string) env('SHOPEE_API_BASE_URL'), '/');

    $connection = \App\Models\ChannelConnection::where('id_channel', 1)
        ->where('status_connection', 'connected')
        ->latest('id_connection')
        ->first();

    if (!$connection) {
        return [
            'success' => false,
            'message' => 'Koneksi Shopee tidak ditemukan',
        ];
    }

    $accessToken = $connection->access_token;
    $shopId = (int) $connection->shop_id;

    $timestamp = time();
    $path = '/api/v2/order/get_order_list';

    $timeFrom = now()->subDays(15)->timestamp;
    $timeTo = now()->timestamp;

    $baseString = $partnerId . $path . $timestamp . $accessToken . $shopId;
    $sign = hash_hmac('sha256', $baseString, $partnerKey);

    $query = http_build_query([
        'partner_id' => $partnerId,
        'timestamp' => $timestamp,
        'access_token' => $accessToken,
        'shop_id' => $shopId,
        'sign' => $sign,
        'time_range_field' => 'create_time',
        'time_from' => $timeFrom,
        'time_to' => $timeTo,
        'page_size' => 20,
        'cursor' => '',
        #'order_status' => 'READY_TO_SHIP',
        #'response_optional_fields' => 'order_status,total_amount,item_list',
    ]);

    $url = $baseUrl . $path . '?' . $query;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'response' => json_decode($response, true),
        'raw_response' => $response,
        'debug' => [
            'shop_id' => $shopId,
            'path' => $path,
            'time_from' => $timeFrom,
            'time_to' => $timeTo,
        ],
    ];
}

public function getOrderDetail(array $orderSnList): array
{
    $partnerId = trim((string) env('SHOPEE_PARTNER_ID'));
    $partnerKey = trim((string) env('SHOPEE_PARTNER_KEY'));
    $baseUrl = rtrim((string) env('SHOPEE_API_BASE_URL'), '/');

    $connection = \App\Models\ChannelConnection::where('id_channel', 1)
        ->where('status_connection', 'connected')
        ->latest('id_connection')
        ->first();

    if (!$connection) {
        return [
            'success' => false,
            'message' => 'Koneksi Shopee tidak ditemukan',
        ];
    }

    $accessToken = $connection->access_token;
    $shopId = (int) $connection->shop_id;

    $timestamp = time();
    $path = '/api/v2/order/get_order_detail';

    $baseString = $partnerId . $path . $timestamp . $accessToken . $shopId;
    $sign = hash_hmac('sha256', $baseString, $partnerKey);

    $query = http_build_query([
        'partner_id' => $partnerId,
        'timestamp' => $timestamp,
        'access_token' => $accessToken,
        'shop_id' => $shopId,
        'sign' => $sign,
        'order_sn_list' => implode(',', $orderSnList),
        'response_optional_fields' => 'item_list,total_amount,order_status,create_time',
    ]);

    $url = $baseUrl . $path . '?' . $query;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'response' => json_decode($response, true),
        'raw_response' => $response,
    ];
}

public function getProductModels(int $itemId): array
{
    $partnerId = trim((string) env('SHOPEE_PARTNER_ID'));
    $partnerKey = trim((string) env('SHOPEE_PARTNER_KEY'));
    $baseUrl = rtrim((string) env('SHOPEE_API_BASE_URL'), '/');

    $connection = \App\Models\ChannelConnection::where('id_channel', 1)
        ->where('status_connection', 'connected')
        ->latest('id_connection')
        ->first();

    if (!$connection) {
        return [
            'success' => false,
            'message' => 'Koneksi Shopee tidak ditemukan',
        ];
    }

    $accessToken = $connection->access_token;
    $shopId = (int) $connection->shop_id;

    $timestamp = time();
    $path = '/api/v2/product/get_model_list';

    $baseString = $partnerId . $path . $timestamp . $accessToken . $shopId;
    $sign = hash_hmac('sha256', $baseString, $partnerKey);

    $query = http_build_query([
        'partner_id' => $partnerId,
        'timestamp' => $timestamp,
        'access_token' => $accessToken,
        'shop_id' => $shopId,
        'sign' => $sign,
        'item_id' => $itemId,
    ]);

    $url = $baseUrl . $path . '?' . $query;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'response' => json_decode($response, true),
        'raw_response' => $response,
        'debug' => [
            'shop_id' => $shopId,
            'item_id' => $itemId,
            'path' => $path,
        ],
    ];
}
}