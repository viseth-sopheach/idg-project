<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerService
{
  public function paginate(array $filters): LengthAwarePaginator
  {
    $query = Customer::query();

    if (! empty($filters['search'])) {
      $search = trim($filters['search']);
      $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';

      $query->where(function ($q) use ($search, $operator) {
        $q->where('name', $operator, "%{$search}%")
          ->orWhere('code', $operator, "%{$search}%")
          ->orWhere('phone', $operator, "%{$search}%");
      });
    }

    $perPage = min((int) ($filters['per_page'] ?? 15), 100);

    return $query->orderBy('name')->paginate($perPage)->withQueryString();
  }

  public function find(int $id): Customer
  {
    return Customer::findOrFail($id);
  }

  public function create(array $data): Customer
  {
    $data['code'] = $this->generateCustomerCode();
    $data['status'] = $data['status'] ?? Customer::STATUS_ACTIVE;

    return Customer::create($data);
  }

  public function update(Customer $customer, array $data): Customer
  {
    $customer->update($data);

    return $customer->refresh();
  }

  public function delete(Customer $customer): void
  {
    $customer->delete();
  }

  protected function generateCustomerCode(): string
  {
    return DB::transaction(function () {
      $sequence = Customer::count() + 1;
      $code = 'CUS-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);

      while (Customer::where('code', $code)->exists()) {
        $code = 'CUS-' . Str::upper(Str::random(6));
      }

      return $code;
    });
  }
}