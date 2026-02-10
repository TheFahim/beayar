<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserCompany;
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
        if (! Session::has('tenant_id')) {
            return false;
        }

        $tenantId = Session::get('tenant_id');

        // Check Global Role (Spatie)
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Check Contextual Role (Company Member)
        $role = $user->roleInCompany($tenantId);

        // Tenant Admin (Owner) OR Company Admin can create members
        return $user->isOwnerOf($tenantId) ||
               $user->hasRole('tenant_admin') ||
               $role === 'company_admin';
    }

    /**
     * Determine whether the user can update member roles.
     */
    public function update(User $user, UserCompany $company): bool
    {
        // Check Global Role
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $role = $user->roleInCompany($company->id);

        // Tenant Admin (Owner) OR Company Admin can update roles
        // BUT Company Admin cannot demote/promote the Owner or other Tenant Admins (logic needs care)
        return $user->isOwnerOf($company->id) ||
               $user->hasRole('tenant_admin') ||
               $role === 'company_admin';
    }

    /**
     * Determine whether the user can remove members.
     */
    public function delete(User $user, UserCompany $company): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $role = $user->roleInCompany($company->id);

        return $user->isOwnerOf($company->id) ||
               $user->hasRole('tenant_admin') ||
               $role === 'company_admin';
    }
}
