<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
   Route::post('/logout', [AuthController::class, 'logout']);
   Route::get('/me', [AuthController::class, 'me']);
   Route::put('/profile', [AuthController::class, 'updateProfile']);
   Route::put('/password', [AuthController::class, 'updatePassword']);

   Route::get('/dashboard', [DashboardController::class, 'index']);

   Route::apiResource('products', ProductController::class);
   Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock']);

   Route::get('/orders', [OrderController::class, 'index']);
   Route::post('/orders', [OrderController::class, 'store']);
   Route::get('/orders/{order}', [OrderController::class, 'show']);
   Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);

   Route::apiResource('customers', CustomerController::class);

   Route::get('/reports/sales', [ReportController::class, 'sales']);
   Route::get('/reports/top-products', [ReportController::class, 'topProducts']);
});
