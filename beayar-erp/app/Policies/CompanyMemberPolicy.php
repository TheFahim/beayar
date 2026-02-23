<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TenantCompany;
use Illuminate\Support\Facades\Session;

class CompanyMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_members') || $user->hasRole('tenant_admin');
    }

    /**
     * Determine whether the user can add members.
     */
    public function create(User $user): bool
    {
        return $user->can('manage_members') || $user->hasRole('tenant_admin');
    }

    /**
     * Determine whether the user can update member roles.
     */
    public function update(User $user, TenantCompany $company): bool
    {
        return ($user->can('manage_members') || $user->hasRole('tenant_admin')) &&
               ($company->id === $user->current_tenant_company_id);
    }

    /**
     * Determine whether the user can remove members.
     */
    public function delete(User $user, TenantCompany $company): bool
    {
        return ($user->can('manage_members') || $user->hasRole('tenant_admin')) &&
               ($company->id === $user->current_tenant_company_id);
    }
}
