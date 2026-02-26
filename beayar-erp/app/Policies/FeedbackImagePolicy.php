<?php

namespace App\Policies;

use App\Models\FeedbackImage;
use App\Models\User;

class FeedbackImagePolicy
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
    public function view(User $user, FeedbackImage $feedbackImage): bool
    {
        return $user->can('view_feedback') &&
            ($feedbackImage->tenant_company_id === $user->current_tenant_company_id);
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
    public function update(User $user, FeedbackImage $feedbackImage): bool
    {
        return $user->can('edit_feedback') &&
            ($feedbackImage->tenant_company_id === $user->current_tenant_company_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FeedbackImage $feedbackImage): bool
    {
        return $user->can('delete_feedback') &&
            ($feedbackImage->tenant_company_id === $user->current_tenant_company_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FeedbackImage $feedbackImage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FeedbackImage $feedbackImage): bool
    {
        return false;
    }
}
