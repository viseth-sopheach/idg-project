<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
  /**
   * List products with optional search and server-side pagination.
   */
  public function paginate(array $filters): LengthAwarePaginator
  {
    $query = Product::query();

    if (! empty($filters['search'])) {
      $search = trim($filters['search']);
      $operator = $this->caseInsensitiveOperator();

      $query->where(function ($q) use ($search, $operator) {
        $q->where('name', $operator, "%{$search}%")
          ->orWhere('sku', $operator, "%{$search}%");
      });
    }

    if (! empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    if (! empty($filters['low_stock'])) {
      $query->where('qty', '<=', (int) config('pos.low_stock_threshold'));
    }

    $sortBy = in_array($filters['sort_by'] ?? null, ['name', 'qty', 'price', 'selling_price', 'created_at'])
      ? $filters['sort_by']
      : 'created_at';

    $sortDirection = ($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

    $perPage = min((int) ($filters['per_page'] ?? 15), 100);

    return $query->orderBy($sortBy, $sortDirection)
      ->paginate($perPage)
      ->withQueryString();
  }

  public function find(int $id): Product
  {
    return Product::findOrFail($id);
  }

  /**
   * PostgreSQL (production) supports ILIKE for case-insensitive search;
   * SQLite (used in tests) does not, but its LIKE is already
   * case-insensitive for ASCII, so it works fine as a fallback.
   */
  protected function caseInsensitiveOperator(): string
  {
    return DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
  }

  public function create(array $data): Product
  {
    $data['selling_price'] = $this->calculateSellingPrice(
      (float) $data['price'],
      $data['discount_type'],
      (float) $data['discount_value']
    );

    return Product::create($data);
  }

  public function update(Product $product, array $data): Product
  {
    $price = array_key_exists('price', $data) ? (float) $data['price'] : (float) $product->price;
    $discountType = $data['discount_type'] ?? $product->discount_type;
    $discountValue = array_key_exists('discount_value', $data) ? (float) $data['discount_value'] : (float) $product->discount_value;

    $data['selling_price'] = $this->calculateSellingPrice($price, $discountType, $discountValue);

    $product->update($data);

    return $product->refresh();
  }

  public function delete(Product $product): void
  {
    $product->delete();
  }

  /**
   * Set, increment, or decrement a product's stock quantity.
   */
  public function updateStock(Product $product, string $operation, int $qty): Product
  {
    return DB::transaction(function () use ($product, $operation, $qty) {
      $locked = Product::query()->lockForUpdate()->findOrFail($product->id);

      $newQty = match ($operation) {
        'set' => $qty,
        'increment' => $locked->qty + $qty,
        'decrement' => max($locked->qty - $qty, 0),
        default => $locked->qty,
      };

      $locked->update(['qty' => $newQty]);

      return $locked->refresh();
    });
  }

  public function calculateSellingPrice(float $price, string $discountType, float $discountValue): float
  {
    $sellingPrice = match ($discountType) {
      'percentage' => $price - ($price * $discountValue / 100),
      'fixed' => $price - $discountValue,
      default => $price,
    };

    return round(max($sellingPrice, 0), 2);
  }
}
