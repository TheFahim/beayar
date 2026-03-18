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
        if (!$user->can('edit_bills')) {
            return false;
        }

        if ($bill->tenant_company_id !== $user->current_tenant_company_id) {
            return false;
        }

        return $bill->canBeEdited();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bill $bill): bool
    {
        // Only draft bills can be deleted
        if ($bill->status !== Bill::STATUS_DRAFT) {
            return false;
        }

        return $user->hasRole('tenant_admin') &&
               ($bill->tenant_company_id === $user->current_tenant_company_id);
    }

    /**
     * Determine whether the user can issue the bill.
     */
    public function issue(User $user, Bill $bill): bool
    {
        if (!$user->can('edit_bills')) {
            return false;
        }

        if ($bill->tenant_company_id !== $user->current_tenant_company_id) {
            return false;
        }

        return $bill->status === Bill::STATUS_DRAFT;
    }

    /**
     * Determine whether the user can cancel the bill.
     */
    public function cancel(User $user, Bill $bill): bool
    {
        if (!$user->can('edit_bills')) {
            return false;
        }

        if ($bill->tenant_company_id !== $user->current_tenant_company_id) {
            return false;
        }

        // Only issued or partially paid bills can be cancelled
        return in_array($bill->status, [
            Bill::STATUS_ISSUED,
            Bill::STATUS_PARTIALLY_PAID,
        ]);
    }

    /**
     * Determine whether the user can record payments.
     */
    public function recordPayment(User $user, Bill $bill): bool
    {
        if (!$user->can('edit_bills')) {
            return false;
        }

        if ($bill->tenant_company_id !== $user->current_tenant_company_id) {
            return false;
        }

        return in_array($bill->status, [
            Bill::STATUS_ISSUED,
            Bill::STATUS_PARTIALLY_PAID,
        ]);
    }

    /**
     * Determine whether the user can apply advance credit.
     */
    public function applyAdvance(User $user, Bill $bill): bool
    {
        if (!$user->can('edit_bills')) {
            return false;
        }

        if ($bill->tenant_company_id !== $user->current_tenant_company_id) {
            return false;
        }

        // Only regular bills can have advance applied
        if ($bill->bill_type !== Bill::TYPE_REGULAR) {
            return false;
        }

        // Advance can be applied to draft, issued, or partially paid bills
        return in_array($bill->status, [
            Bill::STATUS_DRAFT,
            Bill::STATUS_ISSUED,
            Bill::STATUS_PARTIALLY_PAID,
        ]);
    }

    /**
     * Determine whether the user can reissue a cancelled bill.
     */
    public function reissue(User $user, Bill $bill): bool
    {
        if (!$user->can('edit_bills')) {
            return false;
        }

        if ($bill->tenant_company_id !== $user->current_tenant_company_id) {
            return false;
        }

        // Only cancelled bills can be reissued
        return $bill->status === Bill::STATUS_CANCELLED;
    }
}
