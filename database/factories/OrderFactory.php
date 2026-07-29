<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
  protected $model = Order::class;

  public function definition(): array
  {
    $subTotal = $this->faker->randomFloat(2, 20, 1000);
    $discountAmount = round($subTotal * 0.05, 2);
    $deliveryFee = $this->faker->randomFloat(2, 0, 15);
    $totalAmount = round($subTotal - $discountAmount + $deliveryFee, 2);

    return [
      'order_no' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper($this->faker->unique()->bothify('####')),
      'customer_id' => Customer::factory(),
      'order_date' => now()->toDateString(),
      'status' => Order::STATUS_PENDING,
      'sub_total' => $subTotal,
      'discount_amount' => $discountAmount,
      'delivery_fee' => $deliveryFee,
      'total_amount' => $totalAmount,
      'total_paid' => $totalAmount,
    ];
  }
}
