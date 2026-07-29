<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
  public function paginate(array $filters): LengthAwarePaginator
  {
    $query = Order::query()->with('customer')->withCount('items');

    if (! empty($filters['search'])) {
      $search = trim($filters['search']);
      $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';

      $query->where(function ($q) use ($search, $operator) {
        $q->where('order_no', $operator, "%{$search}%")
          ->orWhereHas('customer', function ($customerQuery) use ($search, $operator) {
            $customerQuery->where('name', $operator, "%{$search}%")
              ->orWhere('code', $operator, "%{$search}%");
          });
      });
    }

    if (! empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    if (! empty($filters['customer_id'])) {
      $query->where('customer_id', $filters['customer_id']);
    }

    if (! empty($filters['date_from'])) {
      $query->whereDate('order_date', '>=', $filters['date_from']);
    }

    if (! empty($filters['date_to'])) {
      $query->whereDate('order_date', '<=', $filters['date_to']);
    }

    $perPage = min((int) ($filters['per_page'] ?? 15), 100);

    return $query->orderByDesc('order_date')
      ->orderByDesc('id')
      ->paginate($perPage)
      ->withQueryString();
  }

  public function find(int $id): Order
  {
    return Order::with(['customer', 'items.product'])->findOrFail($id);
  }

  /**
   * Create an order, validate stock for every line item, persist the order
   * and its items, and decrement product stock — all inside a single
   * database transaction. Throws InsufficientStockException (which rolls
   * the transaction back automatically) if any product can't fulfil the
   * requested quantity.
   */
  public function create(array $data): Order
  {
    return DB::transaction(function () use ($data) {
      $productIds = collect($data['items'])->pluck('product_id')->unique()->values();

      // Lock the rows for the duration of the transaction so two
      // simultaneous orders can't oversell the same product.
      $products = Product::query()
        ->whereIn('id', $productIds)
        ->lockForUpdate()
        ->get()
        ->keyBy('id');

      $shortages = [];
      $subTotal = 0;
      $lineItems = [];

      foreach ($data['items'] as $item) {
        /** @var Product $product */
        $product = $products->get($item['product_id']);
        $requestedQty = (int) $item['qty'];

        if (! $product->isInStock($requestedQty)) {
          $shortages[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'requested' => $requestedQty,
            'available' => $product->qty,
          ];

          continue;
        }

        $lineTotal = round((float) $product->selling_price * $requestedQty, 2);
        $subTotal += $lineTotal;

        $lineItems[] = [
          'product' => $product,
          'qty' => $requestedQty,
          'total_price' => $lineTotal,
        ];
      }

      if (! empty($shortages)) {
        throw new InsufficientStockException($shortages);
      }

      $discountAmount = round((float) ($data['discount_amount'] ?? 0), 2);
      $discountAmount = min($discountAmount, $subTotal);

      $deliveryFee = round((float) ($data['delivery_fee'] ?? 0), 2);
      $totalAmount = round($subTotal - $discountAmount + $deliveryFee, 2);
      $totalPaid = round((float) ($data['total_paid'] ?? 0), 2);

      $order = Order::create([
        'order_no' => $this->generateOrderNumber(),
        'customer_id' => $data['customer_id'],
        'order_date' => $data['order_date'] ?? now()->toDateString(),
        'status' => Order::STATUS_PENDING,
        'sub_total' => round($subTotal, 2),
        'discount_amount' => $discountAmount,
        'delivery_fee' => $deliveryFee,
        'total_amount' => $totalAmount,
        'total_paid' => $totalPaid,
      ]);

      foreach ($lineItems as $line) {
        OrderItem::create([
          'order_id' => $order->id,
          'product_id' => $line['product']->id,
          'qty' => $line['qty'],
          'total_price' => $line['total_price'],
        ]);

        $line['product']->decrement('qty', $line['qty']);
      }

      return $order->load(['customer', 'items.product']);
    });
  }

  /**
   * Transition an order's status. Cancelling a pending/completed order
   * automatically restocks its items so inventory stays accurate.
   */
  public function updateStatus(Order $order, string $status): Order
  {
    if ($order->status === $status) {
      return $order;
    }

    return DB::transaction(function () use ($order, $status) {
      $locked = Order::query()->lockForUpdate()->with('items')->findOrFail($order->id);

      if ($status === Order::STATUS_CANCELLED && $locked->status !== Order::STATUS_CANCELLED) {
        foreach ($locked->items as $item) {
          Product::query()->lockForUpdate()->find($item->product_id)?->increment('qty', $item->qty);
        }
      }

      $locked->update(['status' => $status]);

      return $locked->load(['customer', 'items.product']);
    });
  }

  /**
   * Generates a unique
   */
  protected function generateOrderNumber(): string
  {
    $prefix = 'ORD-' . now()->format('Ymd') . '-';

    $lastSequence = Order::query()
      ->where('order_no', 'like', $prefix . '%')
      ->count();

    $sequence = str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);
    $orderNo = $prefix . $sequence;

    while (Order::where('order_no', $orderNo)->exists()) {
      $orderNo = $prefix . Str::upper(Str::random(4));
    }

    return $orderNo;
  }
}
