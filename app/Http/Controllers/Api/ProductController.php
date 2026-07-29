<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\UpdateStockRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
  use ApiResponse;

  public function __construct(protected ProductService $products) {}

  public function index(Request $request)
  {
    $paginator = $this->products->paginate($request->only([
      'search',
      'status',
      'low_stock',
      'sort_by',
      'sort_direction',
      'per_page',
    ]));

    return $this->success(
      ProductResource::collection($paginator->items()),
      'Products retrieved successfully.',
      200,
      [
        'current_page' => $paginator->currentPage(),
        'per_page' => $paginator->perPage(),
        'total' => $paginator->total(),
        'last_page' => $paginator->lastPage(),
      ]
    );
  }

  public function store(StoreProductRequest $request)
  {
    $product = $this->products->create($request->validated());

    return $this->success(new ProductResource($product), 'Product created successfully.', 201);
  }

  public function show(Product $product)
  {
    return $this->success(new ProductResource($product), 'Product retrieved successfully.');
  }

  public function update(UpdateProductRequest $request, Product $product)
  {
    $product = $this->products->update($product, $request->validated());

    return $this->success(new ProductResource($product), 'Product updated successfully.');
  }

  public function destroy(Product $product)
  {
    $this->products->delete($product);

    return $this->success(null, 'Product deleted successfully.');
  }

  public function updateStock(UpdateStockRequest $request, Product $product)
  {
    $product = $this->products->updateStock(
      $product,
      $request->validated('operation'),
      $request->validated('qty')
    );

    return $this->success(new ProductResource($product), 'Stock updated successfully.');
  }
}
