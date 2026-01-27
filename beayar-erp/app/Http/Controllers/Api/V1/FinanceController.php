<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Tenant\FinancialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    protected $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function dashboard(Request $request): JsonResponse
    {
        // $stats = $this->financialService->getDashboardStats($request->user());
        // return response()->json($stats);
        return response()->json(['message' => 'Finance dashboard stats']);
    }

    public function expenses(): JsonResponse
    {
        // List expenses
        return response()->json(['message' => 'Expenses list']);
    }

    public function payments(): JsonResponse
    {
        // List payments
        return response()->json(['message' => 'Payments list']);
    }
}
