<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TenantRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenantId = Auth::user()->current_tenant_company_id;
        
        $roles = Role::where('tenant_company_id', $tenantId)
            ->with('permissions')
            ->get();

        // Get members for assignment dropdown
        // Assuming user has 'companies' relationship mapped correctly
        $members = User::whereHas('companies', function($q) use ($tenantId) {
            $q->where('tenant_companies.id', $tenantId);
        })->get();

        return view('tenant.roles.index', compact('roles', 'members'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch all global permissions (created by Super Admin)
        // Assuming permissions are global and don't have team_id
        $permissions = Permission::whereNull('tenant_company_id')->get();

        return view('tenant.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name', // Validate permission names
        ]);

        $teamId = Auth::user()->current_tenant_company_id;

        // Check if role with same name exists for this team
        if (Role::where('tenant_company_id', $teamId)->where('name', $request->name)->exists()) {
            throw ValidationException::withMessages(['name' => 'A role with this name already exists in your company.']);
        }

        // Create Role scoped to the team
        // We manually set tenant_company_id because Spatie's create() might rely on setPermissionsTeamId global state,
        // but passing it explicitly is safer.
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
            'tenant_company_id' => $teamId,
        ]);

        // Sync Permissions
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('tenant.roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::where('id', $id)->where('tenant_company_id', Auth::user()->current_tenant_company_id)->firstOrFail();
        $permissions = Permission::whereNull('tenant_company_id')->get();
        
        return view('tenant.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::where('id', $id)->where('tenant_company_id', Auth::user()->current_tenant_company_id)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Check uniqueness if name changed
        if ($request->name !== $role->name && Role::where('tenant_company_id', $role->tenant_company_id)->where('name', $request->name)->exists()) {
             throw ValidationException::withMessages(['name' => 'A role with this name already exists in your company.']);
        }

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('tenant.roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::where('id', $id)->where('tenant_company_id', Auth::user()->current_tenant_company_id)->firstOrFail();
        
        // Optional: Prevent deleting if assigned to users?
        if ($role->users()->exists()) {
            return back()->withErrors('Cannot delete role because it is assigned to users.');
        }

        $role->delete();

        return redirect()->route('tenant.roles.index')->with('success', 'Role deleted successfully.');
    }

    /**
     * Assign a role to an employee.
     */
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $teamId = Auth::user()->current_tenant_company_id;

        // Verify Role belongs to this team
        $role = Role::where('id', $request->role_id)->where('tenant_company_id', $teamId)->firstOrFail();

        // Verify User belongs to this team (is a member)
        // We can check via company_members pivot or TenantCompany relationship
        // Assuming user is already added to company_members
        $targetUser = User::whereHas('companies', function($q) use ($teamId) {
            $q->where('tenant_companies.id', $teamId);
        })->findOrFail($request->user_id);

        // Assign Role
        // Spatie's assignRole will use the current team_id from global scope if set,
        // OR we can force it.
        // Since we registered middleware, setPermissionsTeamId is active.
        // But to be explicit and safe:
        setPermissionsTeamId($teamId);
        $targetUser->assignRole($role);

        return back()->with('success', 'Role assigned to employee successfully.');
    }
}
