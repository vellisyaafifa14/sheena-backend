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

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi Shopee tidak ditemukan',
            ], 404);
        }

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

            $product = \App\Models\Product::updateOrCreate(
                ['product_name' => $data['item_name']],
                [
                    'product_slug' => Str::slug($data['item_name']),
                    'product_desc' => $data['description'] ?? '',
                    'product_image' => $data['image']['image_url_list'][0] ?? null,
                    'is_published' => true,
                    'id_category' => null,
                ]
            );

            if (!empty($data['has_model'])) {
                $modelResult = $this->shopeeService->getProductModels($itemId);
                $models = $modelResult['response']['response']['model'] ?? [];

                foreach ($models as $model) {
                    \App\Models\ProductListing::updateOrCreate(
                        [
                            'channel_product_id' => $data['item_id'],
                            'variant_id' => $model['model_id'],
                            'id_connection' => $connection->id_connection,
                        ],
                        [
                            'id_product' => $product->id_product,
                            'variant_name' => $model['model_name'] ?? null,
                            'channel_sku' => $model['model_sku'] ?? null,
                            'price' => $model['price_info'][0]['current_price'] ?? 0,
                            'stock' => $model['stock_info_v2']['summary_info']['total_available_stock'] ?? 0,
                            'product_url' => 'https://shopee.co.id/product/' . $connection->shop_id . '/' . $data['item_id'],
                            'listing_status' => ($model['model_status'] ?? '') === 'MODEL_NORMAL' ? 'active' : 'inactive',
                        ]
                    );
                }
            } else {
                \App\Models\ProductListing::updateOrCreate(
                    [
                        'channel_product_id' => $data['item_id'],
                        'variant_id' => null,
                        'id_connection' => $connection->id_connection,
                    ],
                    [
                        'id_product' => $product->id_product,
                        'variant_name' => null,
                        'channel_sku' => $data['item_sku'] ?? null,
                        'price' => $data['price_info'][0]['current_price'] ?? 0,
                        'stock' => $data['stock_info_v2']['summary_info']['total_available_stock'] ?? 0,
                        'product_url' => null,
                        'listing_status' => 'active',
                    ]
                );
            }

            $saved[] = $product->product_name;
        }

        return response()->json([
            'success' => true,
            'saved_products' => $saved,
        ]);
    }

    public function syncOrders()
    {
        $start = now()->startOfMonth();
$end = now();

$allOrderList = [];

while ($start->lte($end)) {
    $chunkEnd = $start->copy()->addDays(14)->endOfDay();

    if ($chunkEnd->gt($end)) {
        $chunkEnd = $end->copy();
    }

    $cursor = '';

    do {
        $listResult = $this->shopeeService->getOrders(
            $start->timestamp,
            $chunkEnd->timestamp,
            $cursor
        );

        $orders = $listResult['response']['response']['order_list'] ?? [];
        $allOrderList = array_merge($allOrderList, $orders);

        $cursor = $listResult['response']['response']['next_cursor'] ?? '';
        $hasMore = $listResult['response']['response']['more'] ?? false;
    } while ($hasMore && $cursor);

    $start = $chunkEnd->copy()->addSecond();
}

        if (
            !$listResult['success'] ||
            empty($listResult['response']['response']['order_list'])
        ) {
            return response()->json($listResult);
        }

        $connection = \App\Models\ChannelConnection::where('id_channel', 1)
            ->where('status_connection', 'connected')
            ->latest('id_connection')
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi Shopee tidak ditemukan',
            ], 404);
        }

        $orderSnList = collect($allOrderList)
            ->pluck('order_sn')
            ->filter()
            ->values()
            ->toArray();

        $detailResult = $this->shopeeService->getOrderDetail($orderSnList);

        if (
            !$detailResult['success'] ||
            empty($detailResult['response']['response']['order_list'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal ambil detail order',
                'list_result' => $listResult,
                'detail_result' => $detailResult,
            ]);
        }

        $savedOrders = [];
        $skippedItems = [];

        foreach ($detailResult['response']['response']['order_list'] as $orderData) {
            $order = \App\Models\Order::updateOrCreate(
                [
                    'channel_order_id' => $orderData['order_sn'],
                ],
                [
                    'id_connection' => $connection->id_connection,
                    'total_amount' => $orderData['total_amount'] ?? 0,
                    'order_status' => $orderData['order_status'] ?? 'unknown',
                    'ordered_at_channel' => !empty($orderData['create_time'])
                        ? \Carbon\Carbon::createFromTimestamp($orderData['create_time'])
                        : null,
                ]
            );

            foreach (($orderData['item_list'] ?? []) as $item) {
        
                $listingQuery = \App\Models\ProductListing::where('id_connection', $connection->id_connection)
                    ->where('channel_product_id', $item['item_id'] ?? null);

                if (!empty($item['model_id'])) {
                    $listingQuery->where('variant_id', $item['model_id']);
                }

                $listing = $listingQuery->first();

                if (!$listing) {
                    $listing = \App\Models\ProductListing::where('id_connection', $connection->id_connection)
                        ->where('channel_product_id', $item['item_id'] ?? null)
                        ->first();  
                    }

                if (!$listing) {
                    $skippedItems[] = [
                        'order_sn' => $orderData['order_sn'],
                        'item_id' => $item['item_id'] ?? null,
                        'reason' => 'Product listing not found',
                    ];
                    continue;
                }

                \App\Models\OrderItem::updateOrCreate(
                    [
                        'id_order' => $order->id_order,
                        'id_listing' => $listing->id_listing,
                    ],
                    [
                        'price_snapshot' => $item['model_discounted_price']
                            ?? $item['model_original_price']
                            ?? 0,
                        'title_snapshot' => $item['item_name'] ?? '-',
                        'quantity' => $item['model_quantity_purchased']
                            ?? $item['quantity_purchased']
                            ?? 1,
                    ]
                );
            }

            $savedOrders[] = $order->channel_order_id;
        }

        return response()->json([
            'success' => true,
            'saved_orders' => $savedOrders,
            'skipped_items' => $skippedItems,
        ]);
    }
}