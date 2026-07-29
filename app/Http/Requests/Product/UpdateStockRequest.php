<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      // "set" replaces qty outright, "increment"/"decrement" adjust it by the given amount.
      'operation' => ['required', Rule::in(['set', 'increment', 'decrement'])],
      'qty' => ['required', 'integer', 'min:0'],
    ];
  }
}
