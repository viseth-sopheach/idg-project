<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
  use HasFactory;

  public const STATUS_PENDING = 'pending';
  public const STATUS_COMPLETED = 'completed';
  public const STATUS_CANCELLED = 'cancelled';

  public const STATUSES = [
    self::STATUS_PENDING,
    self::STATUS_COMPLETED,
    self::STATUS_CANCELLED,
  ];

  protected $fillable = [
    'order_no',
    'customer_id',
    'order_date',
    'status',
    'sub_total',
    'discount_amount',
    'delivery_fee',
    'total_amount',
    'total_paid',
  ];

  protected function casts(): array
  {
    return [
      'order_date' => 'date',
      'sub_total' => 'decimal:2',
      'discount_amount' => 'decimal:2',
      'delivery_fee' => 'decimal:2',
      'total_amount' => 'decimal:2',
      'total_paid' => 'decimal:2',
    ];
  }

  public function customer(): BelongsTo
  {
    return $this->belongsTo(Customer::class);
  }

  public function items(): HasMany
  {
    return $this->hasMany(OrderItem::class);
  }
}
