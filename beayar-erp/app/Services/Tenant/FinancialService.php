<?php

namespace App\Services\Tenant;

use App\Models\UserCompany;

class FinancialService
{
    public function getDashboardStats(UserCompany $company)
    {
        return [
            'total_revenue' => $company->payments()->sum('amount'),
            'total_expenses' => $company->expenses()->sum('amount'),
            'outstanding_dues' => $company->bills()->where('status', '!=', 'paid')->sum('due'),
            'recent_transactions' => $company->payments()->latest()->limit(5)->get(),
        ];
    }
}
