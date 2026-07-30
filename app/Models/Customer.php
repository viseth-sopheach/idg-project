<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
  use HasFactory;

  public const STATUS_ACTIVE = 'active';
  public const STATUS_INACTIVE = 'inactive';

  public const STATUSES = [
    self::STATUS_ACTIVE,
    self::STATUS_INACTIVE,
  ];

  protected $fillable = [
    'code',
    'name',
    'phone',
    'email',
    'status',
  ];

  public function orders(): HasMany
  {
    return $this->hasMany(Order::class);
  }
}