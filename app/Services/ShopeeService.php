<?php

namespace App\Services;

class ShopeeService
{
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