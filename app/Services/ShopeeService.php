<?php

namespace App\Services;

class ShopeeService
{
    public function getProducts()
    {
        return [
            'success' => true,
            'message' => 'Shopee service is ready',
            'data' => []
        ];
    }
}