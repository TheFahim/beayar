<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;

class FeedbackPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_feedback');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Feedback $feedback): bool
    {
        return $user->can('view_feedback') &&
            ($feedback->tenant_company_id === $user->current_tenant_company_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_feedback');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Feedback $feedback): bool
    {
        return $user->can('edit_feedback') &&
            ($feedback->tenant_company_id === $user->current_tenant_company_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Feedback $feedback): bool
    {
        return $user->can('delete_feedback') &&
            ($feedback->tenant_company_id === $user->current_tenant_company_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Feedback $feedback): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Feedback $feedback): bool
    {
        return false;
    }
}
