<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Revenue and order counts grouped by day within a date range.
     * Cancelled orders are excluded from revenue totals.
     */
    public function salesSummary(?string $dateFrom, ?string $dateTo): array
    {
        $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : now()->subDays(29)->startOfDay();
        $to = $dateTo ? Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        $orders = Order::where('status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('order_date', [$from->toDateString(), $to->toDateString()])
            ->get(['order_date', 'total_amount', 'total_paid']);

        $byDate = $orders->groupBy(fn(Order $order) => $order->order_date->toDateString())
            ->map(fn($group, $date) => [
                'date' => $date,
                'orders_count' => $group->count(),
                'revenue' => round((float)$group->sum('total_amount'), 2),
            ])
            ->sortBy('date')
            ->values();

        return [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'total_orders' => $orders->count(),
            'total_revenue' => round((float)$orders->sum('total_amount'), 2),
            'total_paid' => round((float)$orders->sum('total_paid'), 2),
            'by_date' => $byDate,
        ];
    }

    /**
     * Best-selling products by quantity sold within a date range,
     * excluding items belonging to cancelled orders.
     */
    public function topProducts(?string $dateFrom, ?string $dateTo, int $limit = 10): array
    {
        $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : now()->subDays(29)->startOfDay();
        $to = $dateTo ? Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        return OrderItem::query()
            ->select('product_id')
            ->selectRaw('SUM(qty) as total_qty')
            ->selectRaw('SUM(total_price) as total_revenue')
            ->whereHas('order', function ($query) use ($from, $to) {
                $query->where('status', '!=', Order::STATUS_CANCELLED)
                    ->whereBetween('order_date', [$from->toDateString(), $to->toDateString()]);
            })
            ->with('product:id,name,sku')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->map(fn(OrderItem $item) => [
                'product_id' => $item->product_id,
                'name' => $item->product?->name,
                'sku' => $item->product?->sku,
                'qty_sold' => (int)$item->total_qty,
                'revenue' => round((float)$item->total_revenue, 2),
            ])
            ->values()
            ->toArray();
    }
}
