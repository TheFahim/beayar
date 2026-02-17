<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class QuotationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return Session::has('tenant_id');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Quotation $quotation): bool
    {
        return $user->companies()->where('tenant_company_id', $quotation->tenant_company_id)->exists() ||
               $user->ownedCompanies()->where('id', $quotation->tenant_company_id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if (! Session::has('tenant_id')) {
            return false;
        }

        $tenantId = Session::get('tenant_id');
        $role = $user->roleInCompany($tenantId);

        return $user->isOwnerOf($tenantId) || in_array($role, ['company_admin', 'employee']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Quotation $quotation): bool
    {
        if ($quotation->tenant_company_id != Session::get('tenant_id')) {
            return false;
        }

        $role = $user->roleInCompany($quotation->tenant_company_id);

        return $user->isOwnerOf($quotation->tenant_company_id) || in_array($role, ['company_admin', 'employee']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Quotation $quotation): bool
    {
        if ($quotation->tenant_company_id != Session::get('tenant_id')) {
            return false;
        }

        $role = $user->roleInCompany($quotation->tenant_company_id);

        return $user->isOwnerOf($quotation->tenant_company_id) || $role === 'company_admin';
    }
}
