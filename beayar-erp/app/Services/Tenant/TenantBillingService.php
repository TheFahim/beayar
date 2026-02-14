<?php

namespace App\Services\Tenant;

use App\Models\Bill;
use App\Models\Quotation;
use App\Models\User;

class TenantBillingService
{
    public function createBillFromQuotation(Quotation $quotation, User $user, string $type = 'regular'): Bill
    {
        $activeRevision = $quotation->activeRevision;

        return Bill::create([
            'tenant_company_id' => $user->current_tenant_company_id,
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $activeRevision?->id,
            'bill_type' => $type,
            'invoice_no' => 'INV-'.strtoupper(uniqid()),
            'bill_date' => now(),
            'total_amount' => $activeRevision?->total ?? 0,
            'bill_amount' => $activeRevision?->total ?? 0,
            'due' => $activeRevision?->total ?? 0,
            'status' => 'draft',
        ]);
    }
}
