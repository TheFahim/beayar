<?php

namespace App\Services;

use App\Models\User;
use App\Models\TenantCompany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompanyMemberService
{
    /**
     * Add a member to a company.
     */
    public function addMember(TenantCompany $company, string $email, string $role = 'member', ?string $name = null): User
    {
        // Check member limit
        if ($company->owner && $company->owner->subscription) {
            $limit = $company->owner->subscription->getLimit('employees');
            if ($limit !== -1) {
                 // Count total members across all companies owned by the subscription owner
                 // optimization: could be cached or stored in subscription_usages
                 $currentCount = 0;
                 $ownedCompanies = $company->owner->ownedCompanies;
                 foreach($ownedCompanies as $c) {
                     $currentCount += $c->members()->count();
                 }

                 if ($currentCount >= $limit) {
                     throw ValidationException::withMessages(['email' => 'Employee limit reached for your plan.']);
                 }
            }
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            // Create new user
            $user = User::create([
                'name' => $name ?? explode('@', $email)[0],
                'email' => $email,
                'password' => Hash::make(Str::random(16)), // Random password
                // We might want to mark them as 'invited' or send an email here
            ]);
        }

        if ($company->members()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages(['email' => 'User is already a member of this company.']);
        }

        $company->members()->attach($user->id, ['role' => $role, 'is_active' => true]);

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
