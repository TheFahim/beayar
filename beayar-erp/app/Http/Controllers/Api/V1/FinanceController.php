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
        $stats = $this->financialService->getDashboardStats($request->user()->currentCompany);

        return response()->json($stats);
    }

    public function expenses(Request $request): JsonResponse
    {
        $expenses = $request->user()->currentCompany->expenses()->latest()->paginate(20);

        return response()->json($expenses);
    }

    public function payments(Request $request): JsonResponse
    {
        $payments = $request->user()->currentCompany->payments()->latest()->paginate(20);

        return response()->json($payments);
    }
}
