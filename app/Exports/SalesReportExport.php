<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
{
    public function collection()
    {
        return Order::orderByDesc('ordered_at_channel')->get();
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Order Date',
            'Status',
            'Total Amount',
        ];
    }

    public function map($order): array
    {
        return [
            $order->channel_order_id,
            Carbon::parse($order->ordered_at_channel)->format('Y-m-d'),
            $order->order_status,
            $order->total_amount,
        ];
    }
}