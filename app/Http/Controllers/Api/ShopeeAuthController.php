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

        $baseString = $partnerId . $path . $timestamp;
        $sign = hash_hmac('sha256', $baseString, $partnerKey);

        $authUrl = $baseUrl . $path
            . '?partner_id=' . $partnerId
            . '&timestamp=' . $timestamp
            . '&sign=' . $sign
            . '&redirect=' . rawurlencode($redirectUrl);

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