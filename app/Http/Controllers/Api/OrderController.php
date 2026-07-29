<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
  use ApiResponse;

  public function __construct(protected OrderService $orders) {}

  public function index(Request $request)
  {
    $paginator = $this->orders->paginate($request->only([
      'search',
      'status',
      'customer_id',
      'date_from',
      'date_to',
      'per_page',
    ]));

    return $this->success(
      OrderResource::collection($paginator->items()),
      'Orders retrieved successfully.',
      200,
      [
        'current_page' => $paginator->currentPage(),
        'per_page' => $paginator->perPage(),
        'total' => $paginator->total(),
        'last_page' => $paginator->lastPage(),
      ]
    );
  }

  public function store(StoreOrderRequest $request)
  {
    $order = $this->orders->create($request->validated());

    return $this->success(new OrderResource($order), 'Order created successfully.', 201);
  }

  public function show(Order $order)
  {
    $order = $this->orders->find($order->id);

    return $this->success(new OrderResource($order), 'Order retrieved successfully.');
  }

  public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
  {
    $order = $this->orders->updateStatus($order, $request->validated('status'));

    return $this->success(new OrderResource($order), 'Order status updated successfully.');
  }
}
