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
            'total_products' => \App\Models\Product::count(),
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
    $sales = \App\Models\Order::selectRaw('DATE(created_at) as date, COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_revenue')
        ->groupByRaw('DATE(created_at)')
        ->orderByRaw('DATE(created_at) DESC')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $sales,
    ]);
}

public function latestOrders()
{
    $orders = \App\Models\Order::with(['orderItems.productListing.product'])
        ->orderByDesc('ordered_at_channel')
        ->limit(5)
        ->get();

    return response()->json([
        'success' => true,
        'data' => $orders,
    ]);
}
}