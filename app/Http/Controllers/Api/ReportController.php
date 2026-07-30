<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(protected ReportService $reports)
    {
    }

    public function sales(Request $request)
    {
        $summary = $this->reports->salesSummary(
            $request->query('date_from'),
            $request->query('date_to'),
        );

        return $this->success($summary, 'Sales report retrieved successfully.');
    }

    public function topProducts(Request $request)
    {
        $products = $this->reports->topProducts(
            $request->query('date_from'),
            $request->query('date_to'),
            (int)$request->query('limit', 10),
        );

        return $this->success($products, 'Top products retrieved successfully.');
    }
}
