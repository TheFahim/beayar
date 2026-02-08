<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Session;

class CompanyMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return Session::has('tenant_id');
    }

    /**
     * Determine whether the user can add members.
     */
    public function create(User $user): bool
    {
        if (!Session::has('tenant_id')) {
            return false;
        }

        $tenantId = Session::get('tenant_id');
        $role = $user->roleInCompany($tenantId);

        return $user->isOwnerOf($tenantId) || $role === 'admin';
    }

    /**
     * Determine whether the user can update member roles.
     */
    public function update(User $user, UserCompany $company): bool
    {
        // $company here is the company context, typically passed or inferred.
        // If the policy is checked against the Company object itself:
        
        $role = $user->roleInCompany($company->id);

        return $user->isOwnerOf($company->id) || $role === 'admin';
    }

    /**
     * Determine whether the user can remove members.
     */
    public function delete(User $user, UserCompany $company): bool
    {
        $role = $user->roleInCompany($company->id);

        return $user->isOwnerOf($company->id) || $role === 'admin';
    }
}
