<?php

namespace App\Http\Controllers;

use App\Models\Technology;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('username', '<>', 'developer')->latest()->get();

        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'username' => ['required', 'unique:users,username', 'regex:/^\S+$/u'],
            'designation' => ['nullable', 'min:3'],
            'password' => ['required', 'min:8'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'phone' => ['nullable', 'min:6']
        ],[
            'username.regex' => 'No Space Allowed'
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $this->fileSave($request->photo, 'uploads/users', 'user');
        }

        $createdUser = User::create($validated);
        $createdUser->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('sucess', 'New User Created');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $userRole = User::with('roles')->find($user->id)->roles[0]['name'] ?? 'user';
        return view('dashboard.users.edit', compact('user','userRole'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => ['required'],
            'username' => ['required', "unique:users,username,{$user->id}", 'regex:/^\S+$/u'],
            'designation' => ['nullable', 'min:3'],
            'password' => ['nullable', 'min:8'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'phone' => ['nullable', 'min:6']
        ],[
            'username.regex' => 'No Space Allowed'
        ]);

        if (is_null($validatedData['password'])) {
            unset($validatedData['password']);
        }

        $validatedData['photo'] = $this->fileUpdate($request->photo, 'uploads/users', $user->photo, 'user');

        $user->update($validatedData);

        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('success', 'User Updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    // public function roles(User $user){

    //     $roles = Role::all();

    //     $userRoles = $user->roles->pluck('id')->toArray();

    //     $techs = Technology::all();

    //     $userTechs = $user->technologies->pluck('id')->toArray();

    //     return view('dashboard.users.roles', compact('roles', 'user', 'userRoles', 'techs', 'userTechs'));
    // }


    // public function assignRole(User $user, Request $request){
    //     $user->syncRoles($request->roles);
    //     $user->technologies()->sync($request->technologies);

    //     return redirect()->back()->with('success', 'User Access Updated');
    // }

    public function disable(User $user){
        if($user->is_active == 1){
            $user->update([
                'is_active' => 0
            ]);
        }else{
            $user->update([
                'is_active' => 1
            ]);
        }

        return redirect()->route('users.index')->with('success' , 'User Disabled');
    }
}
