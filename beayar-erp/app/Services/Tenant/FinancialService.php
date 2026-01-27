<?php

namespace App\Services\Tenant;

use App\Models\UserCompany;

class FinancialService
{
    public function getDashboardStats(UserCompany $company)
    {
        return [
            'total_revenue' => $company->bills()->sum('total_amount'), // Need to add relationship to UserCompany
            'outstanding_dues' => $company->bills()->sum('due'),
            // Add expenses logic
        ];
    }
}
