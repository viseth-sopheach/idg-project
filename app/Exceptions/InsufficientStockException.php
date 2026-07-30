<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsufficientStockException extends Exception
{
  /**
   * @param  array<int, array{product_id: int, product_name: string, requested: int, available: int}>  $shortages
   */
  public function __construct(
    protected array $shortages,
    string $message = 'One or more products do not have enough stock available.'
  ) {
    parent::__construct($message);
  }

  public function shortages(): array
  {
    return $this->shortages;
  }

  public function render(Request $request): JsonResponse
  {
    return response()->json([
      'success' => false,
      'message' => $this->getMessage(),
      'errors' => [
        'shortages' => $this->shortages,
      ],
    ], 422);
  }
}
