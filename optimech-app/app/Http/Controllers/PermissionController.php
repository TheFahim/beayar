<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::all();

        return view('dashboard.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validatedPermission = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        $data = [];

        try {

            if ($request->has('action') ?? false) {
                foreach ($request->action as $actionName => $action) {
                    $data[] = [
                        'name' => $validatedPermission['name'].' '.$actionName,
                        'guard_name' => 'web',
                        'group' => $validatedPermission['name'],
                    ];
                }
                Permission::insert($data);
            } else {
                Permission::create([
                    'name' => $validatedPermission['name'],
                    'group' => $validatedPermission['name'],
                ]);
            }

        } catch (\Throwable $th) {
            throw ValidationException::withMessages([
                'name' => 'Permission Already Exists',
            ]);
        }

        return redirect()->route('permissions.index')->with('success', 'Permission created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        return view('dashboard.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission deleted');
    }
}
