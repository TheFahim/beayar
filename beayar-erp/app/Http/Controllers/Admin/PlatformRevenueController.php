<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformInvoice;
use App\Models\PlatformPayment;
use Illuminate\Http\JsonResponse;

class PlatformRevenueController extends Controller
{
    public function index(): JsonResponse
    {
        $revenue = [
            'total_revenue' => PlatformPayment::sum('amount'),
            'monthly_recurring_revenue' => PlatformInvoice::where('status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->sum('total'),
            'recent_payments' => PlatformPayment::with('invoice.user.company')->latest()->limit(10)->get(),
        ];

        return response()->json($revenue);
    }

    public function invoices(): JsonResponse
    {
        $invoices = PlatformInvoice::with(['user.company', 'subscription.plan'])->latest()->paginate(20);
        return response()->json($invoices);
    }
}
