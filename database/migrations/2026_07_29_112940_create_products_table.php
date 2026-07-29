<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('products', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('sku')->unique();
      $table->integer('qty')->default(0);
      $table->text('description')->nullable();
      $table->decimal('cost_price', 12, 2)->default(0);
      $table->decimal('price', 12, 2);
      $table->string('discount_type')->default('none');
      $table->decimal('discount_value', 12, 2)->default(0);
      $table->decimal('selling_price', 12, 2);
      $table->string('status')->default('active');
      $table->timestamps();

      $table->index('name');
      $table->index('status');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('products');
  }
};
