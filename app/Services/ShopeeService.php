<?php

namespace App\Services;

class ShopeeService
{
    public function getAuthUrl(): array
    {
        $partnerId = trim((string) env('SHOPEE_PARTNER_ID'));
        $partnerKey = trim((string) env('SHOPEE_PARTNER_KEY'));
        $redirectUrl = trim((string) env('SHOPEE_REDIRECT_URL'));
        $baseUrl = rtrim((string) env('SHOPEE_BASE_URL'), '/');
    
        $timestamp = time();
        $path = '/api/v2/shop/auth_partner';

        $baseString = $partnerId . $path . $timestamp . $code . $shopId;
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
        $baseUrl = rtrim((string) env('SHOPEE_BASE_URL'), '/');

        $timestamp = time();
        $path = '/api/v2/auth/token/get';

        $baseString = $partnerId . $path . $timestamp;
        $sign = hash_hmac('sha256', $baseString, $partnerKey);

        $url = $baseUrl . $path;

        $payload = [
            'code' => $code,
            'shop_id' => (int) $shopId,
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
}