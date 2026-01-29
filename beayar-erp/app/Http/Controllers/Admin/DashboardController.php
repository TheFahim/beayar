<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformInvoice;
use App\Models\PlatformPayment;
use App\Models\UserCompany;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'mrr' => PlatformInvoice::where('status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
            'total_tenants' => UserCompany::count(),
            'new_tenants' => UserCompany::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $recentActivity = PlatformPayment::with('invoice.user.company')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentActivity'));
    }
}
