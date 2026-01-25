<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();

        return view('dashboard.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy('group');

        return view('dashboard.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => ['required', 'min:3', 'max:30']
        ]);

        $savedRole = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);

        if ($request->permissions ?? false) {
            $savedRole->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.edit', $savedRole->id)->with('success', 'Role Created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {

        $permissions = Permission::all()->groupBy('group');
        $hasPermissions = $role->permissions->pluck('id')->toArray();

        return view('dashboard.roles.edit', compact('role', 'permissions', 'hasPermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {

        $validated = $request->validate([
            'name' => ['required', 'min:3', 'max:30', 'unique:roles,name,' . $role->id]
        ]);

        $role->update([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);

        if ($request->permissions ?? false) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.edit', $role->id)->with('success', 'Role Updated');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->syncPermissions([]);

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role Deleted');
    }

}
