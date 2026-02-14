<?php

namespace App\Services;

use App\Models\User;
use App\Models\TenantCompany;
use Illuminate\Validation\ValidationException;

class CompanyMemberService
{
    /**
     * Add a member to a company.
     */
    public function addMember(TenantCompany $company, string $email, string $role = 'member'): User
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages(['email' => 'User not found.']);
        }

        if ($company->members()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages(['email' => 'User is already a member of this company.']);
        }

        $company->members()->attach($user->id, ['role' => $role]);

        return $user;
    }

    /**
     * Remove a member from a company.
     */
    public function removeMember(TenantCompany $company, User $user): void
    {
        if ($company->owner_id === $user->id) {
            throw ValidationException::withMessages(['user' => 'Cannot remove the company owner.']);
        }

        $company->members()->detach($user->id);
    }

    /**
     * Change a member's role.
     */
    public function updateRole(TenantCompany $company, User $user, string $role): void
    {
        if ($company->owner_id === $user->id) {
            throw ValidationException::withMessages(['user' => 'Cannot change role of the company owner.']);
        }

        $company->members()->updateExistingPivot($user->id, ['role' => $role]);
    }
}
