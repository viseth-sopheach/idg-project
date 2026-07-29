<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;

class DashboardService
{
  public function summary(): array
  {
    $lowStockThreshold = (int) config('pos.low_stock_threshold');

    return [
      'total_products' => Product::count(),
      'total_customers' => Customer::count(),
      'total_orders' => Order::count(),
      'total_revenue' => (float) Order::where('status', '!=', Order::STATUS_CANCELLED)->sum('total_paid'),
      'todays_sales' => (float) Order::where('status', '!=', Order::STATUS_CANCELLED)
        ->whereDate('order_date', Carbon::today())
        ->sum('total_paid'),
      'low_stock_products' => Product::query()
        ->where('qty', '<=', $lowStockThreshold)
        ->orderBy('qty')
        ->limit(10)
        ->get(['id', 'name', 'sku', 'qty'])
        ->map(fn(Product $product) => [
          'id' => $product->id,
          'name' => $product->name,
          'sku' => $product->sku,
          'qty' => $product->qty,
        ]),
      'low_stock_count' => Product::where('qty', '<=', $lowStockThreshold)->count(),
    ];
  }
}
