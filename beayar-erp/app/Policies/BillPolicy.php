<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class BillPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_bills');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bill $bill): bool
    {
        return $user->can('view_bills') &&
               ($bill->tenant_company_id === $user->current_tenant_company_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_bills');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bill $bill): bool
    {
        return $user->can('edit_bills') &&
               ($bill->tenant_company_id === $user->current_tenant_company_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bill $bill): bool
    {
        // Typically bills shouldn't be deleted easily, but if permission exists:
        // We didn't define delete_bills in seeder, so maybe restrict to owner or admin role check as fallback?
        // Or assume edit_bills covers deletion? Or prevent deletion.
        // Let's check seeder: 'view_bills', 'create_bills', 'edit_bills', 'view_finance'.
        // No delete_bills.
        // So only super_admin or tenant_admin might delete if logic permits.
        
        return $user->hasRole('tenant_admin') && 
               ($bill->tenant_company_id === $user->current_tenant_company_id);
    }
}
