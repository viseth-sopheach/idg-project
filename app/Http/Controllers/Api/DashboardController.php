<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Support\ApiResponse;

class DashboardController extends Controller
{
  use ApiResponse;

  public function __construct(protected DashboardService $dashboard) {}

  public function index()
  {
    return $this->success($this->dashboard->summary(), 'Dashboard summary retrieved successfully.');
  }
}
