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
    $threeMonthsStart = now()->subMonths(3)->startOfDay();
    $now = now()->endOfDay();

    $currentMonthStart = now()->startOfMonth();
    $currentMonthEnd = now()->endOfDay();

    $previousMonthStart = now()->subMonth()->startOfMonth();
    $previousMonthEnd = now()->subMonth()->endOfMonth();

    $totalRevenue = \App\Models\Order::whereBetween('ordered_at_channel', [$threeMonthsStart, $now])
        ->sum('total_amount');

    $totalOrders = \App\Models\Order::whereBetween('ordered_at_channel', [$threeMonthsStart, $now])
        ->count();

    $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

    $currentRevenue = \App\Models\Order::whereBetween('ordered_at_channel', [$currentMonthStart, $currentMonthEnd])
        ->sum('total_amount');

    $currentOrders = \App\Models\Order::whereBetween('ordered_at_channel', [$currentMonthStart, $currentMonthEnd])
        ->count();

    $previousRevenue = \App\Models\Order::whereBetween('ordered_at_channel', [$previousMonthStart, $previousMonthEnd])
        ->sum('total_amount');

    $previousOrders = \App\Models\Order::whereBetween('ordered_at_channel', [$previousMonthStart, $previousMonthEnd])
        ->count();

    $currentAvgOrder = $currentOrders > 0 ? $currentRevenue / $currentOrders : 0;
    $previousAvgOrder = $previousOrders > 0 ? $previousRevenue / $previousOrders : 0;

    $growth = function ($current, $previous) {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    };

    return response()->json([
        'success' => true,
        'data' => [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'avg_order_value' => $avgOrderValue,

            'revenue_growth' => $growth($currentRevenue, $previousRevenue),
            'orders_growth' => $growth($currentOrders, $previousOrders),
            'avg_order_growth' => $growth($currentAvgOrder, $previousAvgOrder),

            'period_label' => 'Last 3 Months',
            'growth_label' => 'vs previous month',
        ]
    ]);
}
public function reportsSales()
{
    $start = now()->subMonths(3)->startOfDay();
    $end = now()->endOfDay();

    $sales = \App\Models\Order::selectRaw('
            DATE_FORMAT(ordered_at_channel, "%Y-%m") as month_key,
            DATE_FORMAT(ordered_at_channel, "%M %Y") as month,
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount), 0) as total_revenue
        ')
        ->whereNotNull('ordered_at_channel')
        ->whereBetween('ordered_at_channel', [$start, $end])
        ->groupByRaw('DATE_FORMAT(ordered_at_channel, "%Y-%m"), DATE_FORMAT(ordered_at_channel, "%M %Y")')
        ->orderByRaw('DATE_FORMAT(ordered_at_channel, "%Y-%m") DESC')
        ->get();

    $sales = $sales->map(function ($item, $index) use ($sales) {
        $previous = $sales[$index + 1] ?? null;

        if (!$previous || $previous->total_revenue == 0) {
            $growth = null;
        } else {
            $growth = round((($item->total_revenue - $previous->total_revenue) / $previous->total_revenue) * 100, 1);
        }

        return [
            'month_key' => $item->month_key,
            'month' => $item->month,
            'total_orders' => $item->total_orders,
            'total_revenue' => $item->total_revenue,
            'growth' => $growth,
        ];
    });

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