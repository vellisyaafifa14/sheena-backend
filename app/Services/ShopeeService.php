<?php

namespace App\Services;

class ShopeeService
{
    public function getAuthUrl(): array
    {
        $partnerId = env('SHOPEE_PARTNER_ID');
        $partnerKey = trim(env('SHOPEE_PARTNER_KEY'));
        $redirectUrl = env('SHOPEE_REDIRECT_URL');
        $baseUrl = rtrim(env('SHOPEE_BASE_URL'), '/');

        $timestamp = time();
        $path = '/api/v2/shop/auth_partner';

        $baseString = $partnerId . $path . $timestamp;
        $sign = hash_hmac('sha256', $baseString, $partnerKey);

        $authUrl = $baseUrl . $path . '?' . http_build_query([
            'partner_id' => $partnerId,
            'timestamp' => $timestamp,
            'sign' => $sign,
            'redirect' => $redirectUrl,
        ]);

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
}