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
    public function addMember(TenantCompany $company, string $email, string $role = 'member', ?string $name = null, ?string $password = null): User
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
                'password' => Hash::make($password ?? Str::random(16)),
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
     * @deprecated Use updateMember instead.
     */
    public function updateRole(TenantCompany $company, User $user, string $role): void
    {
        $this->updateMember($company, $user, ['role' => $role]);
    }

    /**
     * Update member details.
     */
    public function updateMember(TenantCompany $company, User $user, array $data): void
    {
        // 1. Update User info (name, phone)
        // Only allow updating basic info if the user belongs to this tenant/company context
        // OR if the editor is the owner.
        // For now, let's assume the company admin can update the user's name/phone if they are a member.

        $userUpdates = [];
        if (array_key_exists('name', $data)) $userUpdates['name'] = $data['name'];
        if (array_key_exists('email', $data)) $userUpdates['email'] = $data['email'];
        if (array_key_exists('phone', $data)) $userUpdates['phone'] = $data['phone'];

        if (!empty($userUpdates)) {
            $user->update($userUpdates);
        }

        // 2. Update Membership info (role, is_active, joined_at)
        // Check if updating owner
        if ($company->owner_id === $user->id) {
            // Cannot change role or status of owner
            if (isset($data['role']) && $data['role'] !== 'company_admin') {
                throw ValidationException::withMessages(['user' => 'Cannot change role of the company owner.']);
            }
            if (isset($data['is_active']) && !$data['is_active']) {
                throw ValidationException::withMessages(['user' => 'Cannot deactivate the company owner.']);
            }
        }

        $pivotUpdates = [];
        if (array_key_exists('role', $data)) $pivotUpdates['role'] = $data['role'];
        if (array_key_exists('is_active', $data)) $pivotUpdates['is_active'] = $data['is_active'];
        if (array_key_exists('joined_at', $data)) $pivotUpdates['joined_at'] = $data['joined_at'];

        if (!empty($pivotUpdates)) {
            $company->members()->updateExistingPivot($user->id, $pivotUpdates);
        }
    }
}
