<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
  use HasFactory;

  public const DISCOUNT_NONE = 'none';
  public const DISCOUNT_PERCENTAGE = 'percentage';
  public const DISCOUNT_FIXED = 'fixed';

  public const STATUS_ACTIVE = 'active';
  public const STATUS_INACTIVE = 'inactive';

  protected $fillable = [
    'name',
    'sku',
    'qty',
    'description',
    'cost_price',
    'price',
    'discount_type',
    'discount_value',
    'selling_price',
    'status',
  ];

  protected function casts(): array
  {
    return [
      'qty' => 'integer',
      'cost_price' => 'decimal:2',
      'price' => 'decimal:2',
      'discount_value' => 'decimal:2',
      'selling_price' => 'decimal:2',
    ];
  }

  public function orderItems(): HasMany
  {
    return $this->hasMany(OrderItem::class);
  }

  public function isInStock(int $requiredQty): bool
  {
    return $this->qty >= $requiredQty;
  }
}
