<?php

namespace Database\Factories;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
  protected $model = Product::class;

  public function definition(): array
  {
    $price = $this->faker->randomFloat(2, 5, 500);
    $discountType = $this->faker->randomElement(['none', 'percentage', 'fixed']);
    $discountValue = match ($discountType) {
      'percentage' => $this->faker->randomFloat(2, 0, 30),
      'fixed' => $this->faker->randomFloat(2, 0, $price * 0.2),
      default => 0,
    };

    return [
      'name' => ucfirst($this->faker->words(3, true)),
      'sku' => strtoupper($this->faker->unique()->bothify('SKU-####??')),
      'qty' => $this->faker->numberBetween(0, 200),
      'description' => $this->faker->sentence(),
      'cost_price' => round($price * 0.6, 2),
      'price' => $price,
      'discount_type' => $discountType,
      'discount_value' => $discountValue,
      'selling_price' => app(ProductService::class)->calculateSellingPrice($price, $discountType, $discountValue),
      'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']),
    ];
  }
}
