<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShopeeService;
use Illuminate\Support\Str;

class ShopeeSyncController extends Controller
{
    protected ShopeeService $shopeeService;

    public function __construct(ShopeeService $shopeeService)
    {
        $this->shopeeService = $shopeeService;
    }

    public function syncProducts()
{
    $listResult = $this->shopeeService->getProducts();

    if (
        !$listResult['success'] ||
        empty($listResult['response']['response']['item'])
    ) {
        return response()->json($listResult);
    }

    $connection = \App\Models\ChannelConnection::where('id_channel', 1)
        ->where('status_connection', 'connected')
        ->latest('id_connection')
        ->first();

    $items = $listResult['response']['response']['item'];

    $saved = [];

    foreach ($items as $item) {
        $itemId = $item['item_id'];

        $detail = $this->shopeeService->getProductDetail($itemId);

        if (
            !$detail['success'] ||
            empty($detail['response']['response']['item_list'][0])
        ) {
            continue;
        }

        $data = $detail['response']['response']['item_list'][0];

        // ===== PRODUCT =====
        $product = \App\Models\Product::updateOrCreate(
            ['product_name' => $data['item_name']],
            [
                'product_slug' => \Str::slug($data['item_name']),
                'product_desc' => $data['description_info']['extended_description']['field_list'][0]['text'] ?? '',
                'product_image' => $data['image']['image_url_list'][0] ?? null,
                'is_published' => true,
                'id_category' => null,
            ]
        );

        // ===== LISTING =====
        \App\Models\ProductListing::updateOrCreate(
            [
                'channel_product_id' => $data['item_id'],
                'id_connection' => $connection->id_connection,
            ],
            [
                'id_product' => $product->id_product,
                'price' => $data['price_info'][0]['current_price'] ?? 0,
                'stock' => $data['stock_info_v2']['summary_info']['total_available_stock'] ?? 0,
                'listing_status' => 'active',
                'variant_name' => null,
                'channel_sku' => null,
                'product_url' => null,
            ]
        );

        $saved[] = $product->product_name;
    }

    return response()->json([
        'success' => true,
        'saved_products' => $saved,
    ]);
}

public function syncOrders()
{
    $result = $this->shopeeService->getOrders();

    return response()->json($result);
}
}