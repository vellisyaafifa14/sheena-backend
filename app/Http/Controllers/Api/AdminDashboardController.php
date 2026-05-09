<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ChannelConnection;
use App\Models\Order;
use App\Models\OrderItem;

class AdminDashboardController extends Controller
{
    public function summary()
{
    return response()->json([
        'success' => true,
        'data' => [
            'total_products' => \App\Models\ProductListing::sum('stock') ?? 0,
            'total_listings' => \App\Models\ProductListing::count(),
            'total_orders' => \App\Models\Order::count(),
            'total_revenue' => \App\Models\Order::sum('total_amount') ?? 0,
            'total_items_sold' => \App\Models\OrderItem::sum('quantity') ?? 0,
        ]
    ]);
}
    public function sales()
{
    return response()->json([
        'success' => true,
        'data' => [
            'total_orders' => \App\Models\Order::count(),
            'total_items_sold' => \App\Models\OrderItem::sum('quantity') ?? 0,
            'total_revenue' => \App\Models\Order::sum('total_amount') ?? 0,
        ]
    ]);
}

public function reportsSales()
{
    $sales = \App\Models\Order::selectRaw('
            DATE(ordered_at_channel) as date,
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount), 0) as total_revenue
        ')
        ->whereNotNull('ordered_at_channel')
        ->groupByRaw('DATE(ordered_at_channel)')
        ->orderByRaw('DATE(ordered_at_channel) DESC')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $sales,
    ]);
}

public function latestOrders()
{
    $orders = \App\Models\Order::with('orderItems')
        ->orderByDesc('ordered_at_channel')
        ->limit(5)
        ->get();

    return response()->json([
        'success' => true,
        'data' => $orders,
    ]);
}

public function bestSellingProducts()
{
    $products = \App\Models\OrderItem::with('productListing.product')
        ->selectRaw('
            id_listing,
            SUM(quantity) as total_sold,
            AVG(price_snapshot) as avg_price
        ')
        ->groupBy('id_listing')
        ->orderByDesc('total_sold')
        ->limit(5)
        ->get()
        ->map(function ($item) {
            return [
                'product_name' =>
                    $item->productListing?->product?->product_name
                    ?? $item->title_snapshot
                    ?? 'Unknown Product',

                'product_image' =>
                    $item->productListing?->product?->product_image
                    ?? null,

                'total_sold' => $item->total_sold,

                'avg_price' => $item->avg_price,
            ];
        });

    return response()->json([
        'success' => true,
        'data' => $products,
    ]);
}
}