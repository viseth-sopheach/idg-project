<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $productId = $this->route('product')?->id;

    return [
      'name' => ['sometimes', 'required', 'string', 'max:255'],
      'sku' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
      'qty' => ['sometimes', 'required', 'integer', 'min:0'],
      'description' => ['nullable', 'string'],
      'cost_price' => ['sometimes', 'required', 'numeric', 'min:0'],
      'price' => ['sometimes', 'required', 'numeric', 'min:0'],
      'discount_type' => ['sometimes', 'required', Rule::in(['none', 'percentage', 'fixed'])],
      'discount_value' => ['sometimes', 'required', 'numeric', 'min:0'],
      'status' => ['sometimes', 'required', Rule::in(['active', 'inactive'])],
    ];
  }

  public function withValidator($validator): void
  {
    $validator->after(function ($validator) {
      $product = $this->route('product');

      $discountType = $this->input('discount_type', $product?->discount_type);
      $discountValue = $this->input('discount_value', $product?->discount_value);
      $price = $this->input('price', $product?->price);

      if ($discountType === 'percentage' && $discountValue > 100) {
        $validator->errors()->add('discount_value', 'Percentage discount cannot exceed 100.');
      }

      if ($discountType === 'fixed' && $discountValue > $price) {
        $validator->errors()->add('discount_value', 'Fixed discount cannot exceed the product price.');
      }
    });
  }

  public function messages(): array
  {
    return [
      'sku.unique' => 'This SKU is already used by another product.',
    ];
  }
}
