<?php

namespace App\Policies;

use App\Models\Challan;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class ChallanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_challans');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Challan $challan): bool
    {
        return $user->can('view_challans') &&
               ($challan->tenant_company_id === $user->current_tenant_company_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_challans');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Challan $challan): bool
    {
        return $user->can('edit_challans') &&
               ($challan->tenant_company_id === $user->current_tenant_company_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Challan $challan): bool
    {
        return $user->can('delete_challans') &&
               ($challan->tenant_company_id === $user->current_tenant_company_id);
    }
}
