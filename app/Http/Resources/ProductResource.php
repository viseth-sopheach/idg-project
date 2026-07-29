<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'sku' => $this->sku,
      'qty' => $this->qty,
      'description' => $this->description,
      'cost_price' => (float) $this->cost_price,
      'price' => (float) $this->price,
      'discount_type' => $this->discount_type,
      'discount_value' => (float) $this->discount_value,
      'selling_price' => (float) $this->selling_price,
      'status' => $this->status,
      'is_low_stock' => $this->qty <= (int) config('pos.low_stock_threshold'),
      'created_at' => $this->created_at?->toIso8601String(),
      'updated_at' => $this->updated_at?->toIso8601String(),
    ];
  }
}
