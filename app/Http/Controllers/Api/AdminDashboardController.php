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
                'total_products' => Product::count(),
                'total_channel_connections' => ChannelConnection::count(),
                'total_orders' => Order::count(),
                'total_order_items' => OrderItem::count(),
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
}