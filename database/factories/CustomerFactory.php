<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
  protected $model = Customer::class;

  public function definition(): array
  {
    return [
      'code' => 'CUS-' . str_pad((string) $this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
      'name' => $this->faker->name(),
      'phone' => $this->faker->numerify('09########'),
      'email' => $this->faker->unique()->safeEmail(),
    ];
  }
}
