<?php

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $customerId = $this->route('customer')?->id;

    return [
      'name' => ['sometimes', 'required', 'string', 'max:255'],
      'phone' => ['nullable', 'string', 'max:30'],
      'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customerId)],
      'status' => ['sometimes', Rule::in(Customer::STATUSES)],
    ];
  }
}