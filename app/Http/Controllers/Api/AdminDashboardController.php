<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ChannelConnection;
use App\Models\Order;
use App\Models\OrderItem;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use Illuminate\Http\Request;

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

    $orders = \App\Models\Order::whereNotNull('ordered_at_channel')
        ->whereBetween('ordered_at_channel', [$start, $end])
        ->orderByDesc('ordered_at_channel')
        ->get();

    $grouped = $orders->groupBy(function ($order) {
        return \Carbon\Carbon::parse($order->ordered_at_channel)->format('Y-m');
    });

    $monthly = $grouped->map(function ($monthOrders, $monthKey) {
        return [
            'month_key' => $monthKey,
            'month' => \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('F Y'),
            'total_orders' => $monthOrders->count(),
            'total_revenue' => $monthOrders->sum('total_amount'),
            'orders' => $monthOrders->map(function ($order) {
                return [
                    'order_sn' => $order->channel_order_id,
                    'date' => \Carbon\Carbon::parse($order->ordered_at_channel)->format('Y-m-d'),
                    'status' => $order->order_status,
                    'total_amount' => $order->total_amount,
                ];
            })->values(),
        ];
    })->sortByDesc('month_key')->values();

    $monthly = $monthly->map(function ($item, $index) use ($monthly) {
        $previous = $monthly[$index + 1] ?? null;

        if (!$previous || $previous['total_revenue'] == 0) {
            $item['growth'] = null;
        } else {
            $item['growth'] = round((($item['total_revenue'] - $previous['total_revenue']) / $previous['total_revenue']) * 100, 1);
        }

        return $item;
    });

    return response()->json([
        'success' => true,
        'data' => $monthly,
    ]);
}


public function exportSalesReport(Request $request)
{
    return Excel::download(
        new SalesReportExport(
            $request->start_date,
            $request->end_date
        ),
        'sales_report.xlsx'
    );
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
    $items = \App\Models\OrderItem::with('productListing.product')->get();

    $products = $items
        ->groupBy(function ($item) {
            return $item->productListing?->id_product
                ?? $item->title_snapshot;
        })
        ->map(function ($group) {
            $first = $group->first();
            $product = $first->productListing?->product;

            return [
                'product_name' =>
                    $product?->product_name
                    ?? $first->title_snapshot
                    ?? 'Unknown Product',

                'product_image' =>
                    $product?->product_image
                    ?? null,

                'total_sold' => $group->sum('quantity'),
                'avg_price' => $group->avg('price_snapshot'),
            ];
        })
        ->sortByDesc('total_sold')
        ->take(5)
        ->values();

    return response()->json([
        'success' => true,
        'data' => $products,
    ]);
}
}