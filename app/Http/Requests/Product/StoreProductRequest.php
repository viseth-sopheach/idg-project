<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => ['required', 'string', 'max:255'],
      'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
      'qty' => ['required', 'integer', 'min:0'],
      'description' => ['nullable', 'string'],
      'cost_price' => ['required', 'numeric', 'min:0'],
      'price' => ['required', 'numeric', 'min:0'],
      'discount_type' => ['required', Rule::in(['none', 'percentage', 'fixed'])],
      'discount_value' => ['required', 'numeric', 'min:0'],
      'status' => ['required', Rule::in(['active', 'inactive'])],
    ];
  }

  public function withValidator($validator): void
  {
    $validator->after(function ($validator) {
      if ($this->input('discount_type') === 'percentage' && $this->input('discount_value') > 100) {
        $validator->errors()->add('discount_value', 'Percentage discount cannot exceed 100.');
      }

      if ($this->input('discount_type') === 'fixed' && $this->input('discount_value') > $this->input('price')) {
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
