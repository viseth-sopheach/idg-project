<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
  use ApiResponse;

  public function __construct(protected CustomerService $customers) {}

  public function index(Request $request)
  {
    $paginator = $this->customers->paginate($request->only(['search', 'per_page']));

    return $this->success(
      CustomerResource::collection($paginator->items()),
      'Customers retrieved successfully.',
      200,
      [
        'current_page' => $paginator->currentPage(),
        'per_page' => $paginator->perPage(),
        'total' => $paginator->total(),
        'last_page' => $paginator->lastPage(),
      ]
    );
  }

  public function store(StoreCustomerRequest $request)
  {
    $customer = $this->customers->create($request->validated());

    return $this->success(new CustomerResource($customer), 'Customer created successfully.', 201);
  }
}
