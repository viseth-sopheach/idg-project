<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'product_id' => $this->product_id,
      'product_name' => $this->product?->name,
      'product_sku' => $this->product?->sku,
      'qty' => $this->qty,
      'unit_price' => $this->qty > 0 ? round((float) $this->total_price / $this->qty, 2) : 0,
      'total_price' => (float) $this->total_price,
    ];
  }
}
