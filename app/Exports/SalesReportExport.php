<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithCustomStartCell
{
    protected $startDate;
    protected $endDate;
    protected $orders;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        $this->orders = Order::whereNotNull('ordered_at_channel')
            ->whereBetween('ordered_at_channel', [
                $this->startDate,
                $this->endDate
            ])
            ->orderByDesc('ordered_at_channel')
            ->get();
    }

    public function collection()
    {
        return $this->orders;
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
            strtoupper($order->order_status),
            'Rp ' . number_format($order->total_amount, 0, ',', '.'),
        ];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function styles(Worksheet $sheet)
    {
        $totalRevenue = $this->orders->sum('total_amount');
        $totalOrders = $this->orders->count();
        $avgOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // === TITLE ===
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'Sales Report');

        $sheet->setCellValue('A2', 'Period');
        $sheet->setCellValue(
            'B2',
            Carbon::parse($this->startDate)->format('d M Y') .
            ' - ' .
            Carbon::parse($this->endDate)->format('d M Y')
        );

        // === SUMMARY ===
        $sheet->setCellValue('A3', 'Total Revenue');
        $sheet->setCellValue('B3', 'Rp ' . number_format($totalRevenue, 0, ',', '.'));

        $sheet->setCellValue('A4', 'Total Orders');
        $sheet->setCellValue('B4', $totalOrders);

        $sheet->setCellValue('A5', 'Average Order');
        $sheet->setCellValue('B5', 'Rp ' . number_format($avgOrder, 0, ',', '.'));

        // === STYLE TITLE ===
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
        ]);

        // === STYLE HEADER TABLE ===
        $sheet->getStyle('A6:D6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '1F4E78'],
            ],
        ]);

        // === BORDER DATA ===
        $highestRow = $sheet->getHighestRow();

        $sheet->getStyle("A6:D{$highestRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

        return [];
    }
}