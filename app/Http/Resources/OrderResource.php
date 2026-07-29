<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'order_no' => $this->order_no,
      'order_date' => $this->order_date?->toDateString(),
      'status' => $this->status,
      'customer' => new CustomerResource($this->whenLoaded('customer')),
      'items' => OrderItemResource::collection($this->whenLoaded('items')),
      'items_count' => $this->whenCounted('items'),
      'sub_total' => (float) $this->sub_total,
      'discount_amount' => (float) $this->discount_amount,
      'delivery_fee' => (float) $this->delivery_fee,
      'total_amount' => (float) $this->total_amount,
      'total_paid' => (float) $this->total_paid,
      'balance_due' => round((float) $this->total_amount - (float) $this->total_paid, 2),
      'created_at' => $this->created_at?->toIso8601String(),
      'updated_at' => $this->updated_at?->toIso8601String(),
    ];
  }
}
