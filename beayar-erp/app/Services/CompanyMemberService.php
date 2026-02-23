<?php

namespace App\Services;

use App\Models\User;
use App\Models\TenantCompany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class CompanyMemberService
{
    /**
     * Add a member to a company.
     * @param string|array $role Single role string or array of roles
     */
    public function addMember(TenantCompany $company, string $email, string|array $role = 'member', ?string $name = null, ?string $password = null, array $extraData = []): User
    {
        // Normalize roles to array
        $roles = is_array($role) ? $role : [$role];

        // Determine primary role for pivot table (backward compatibility)
        // Prefer 'company_admin' if present, otherwise first role or 'employee'
        $primaryRole = in_array('company_admin', $roles) ? 'company_admin' : ($roles[0] ?? 'employee');

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
                'avatar' => $extraData['avatar'] ?? null,
                // We might want to mark them as 'invited' or send an email here
            ]);
        } else {
            // If user exists, optionally update avatar if provided
            if (isset($extraData['avatar'])) {
                $user->update(['avatar' => $extraData['avatar']]);
            }
        }

        if ($company->members()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages(['email' => 'User is already a member of this company.']);
        }

        $pivotData = [
            'role' => $primaryRole,
            'is_active' => true,
            'employee_id' => $extraData['employee_id'] ?? null,
        ];

        $company->members()->attach($user->id, $pivotData);

        // Assign Spatie Roles
        // Resolve roles manually to include global roles
        $roleObjects = Role::whereIn('name', $roles)
            ->where(function ($q) use ($company) {
                $q->where('tenant_company_id', $company->id)
                  ->orWhereNull('tenant_company_id');
            })
            ->get();

        setPermissionsTeamId($company->id);
        $user->syncRoles($roleObjects);

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

        // Remove Spatie Role
        setPermissionsTeamId($company->id);
        $roleName = $user->roleInCompany($company->id);
        if ($roleName) {
            $user->removeRole($roleName);
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
        if (array_key_exists('avatar', $data)) $userUpdates['avatar'] = $data['avatar'];
        if (array_key_exists('password', $data)) $userUpdates['password'] = Hash::make($data['password']);

        if (!empty($userUpdates)) {
            $user->update($userUpdates);
        }

        // 2. Update Membership info (role, is_active, joined_at, employee_id)
        $pivotUpdates = [];

        // Handle Role Updates
        if (array_key_exists('roles', $data) || array_key_exists('role', $data)) {
             $inputRole = $data['roles'] ?? $data['role'];
             $roles = is_array($inputRole) ? $inputRole : [$inputRole];

             // Check if updating owner and role change is attempted
             if ($company->owner_id === $user->id) {
                 if (!in_array('company_admin', $roles)) {
                      throw ValidationException::withMessages(['user' => 'Company owner must retain Admin role.']);
                 }
                 // Ensure company_admin is primary
                 $primaryRole = 'company_admin';
             } else {
                 $primaryRole = in_array('company_admin', $roles) ? 'company_admin' : ($roles[0] ?? 'employee');
             }

             $pivotUpdates['role'] = $primaryRole;

             // Sync Spatie Roles
             // Resolve roles manually to include global roles
             $roleObjects = Role::whereIn('name', $roles)
                 ->where(function ($q) use ($company) {
                     $q->where('tenant_company_id', $company->id)
                       ->orWhereNull('tenant_company_id');
                 })
                 ->get();

             setPermissionsTeamId($company->id);
             $user->syncRoles($roleObjects);
        }

        // Handle Status Updates
        if (array_key_exists('is_active', $data)) {
             if ($company->owner_id === $user->id && !$data['is_active']) {
                  throw ValidationException::withMessages(['user' => 'Cannot deactivate the company owner.']);
             }
             $pivotUpdates['is_active'] = $data['is_active'];
        }

        if (array_key_exists('joined_at', $data)) $pivotUpdates['joined_at'] = $data['joined_at'];
        if (array_key_exists('employee_id', $data)) $pivotUpdates['employee_id'] = $data['employee_id'];

        if (!empty($pivotUpdates)) {
            $company->members()->updateExistingPivot($user->id, $pivotUpdates);
        }
    }
}
