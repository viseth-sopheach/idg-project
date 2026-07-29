<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'customer_id' => ['required', 'integer', 'exists:customers,id'],
      'order_date' => ['sometimes', 'date'],
      'discount_amount' => ['sometimes', 'numeric', 'min:0'],
      'delivery_fee' => ['sometimes', 'numeric', 'min:0'],
      'total_paid' => ['sometimes', 'numeric', 'min:0'],

      'items' => ['required', 'array', 'min:1'],
      'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
      'items.*.qty' => ['required', 'integer', 'min:1'],
    ];
  }

  public function messages(): array
  {
    return [
      'items.required' => 'An order must contain at least one product.',
      'items.min' => 'An order must contain at least one product.',
      'items.*.product_id.exists' => 'One of the selected products no longer exists.',
      'items.*.qty.min' => 'Product quantity must be at least 1.',
    ];
  }
}
