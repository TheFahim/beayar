<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show the tenant profile.
     */
    public function show()
    {
        $user = Auth::user();
        return view('tenant.profile.show', compact('user'));
    }

    /**
     * Show the form for editing the tenant profile.
     */
    public function edit()
    {
        $user = Auth::user();

        // Authorization check: Ideally, any user should be able to edit their own profile,
        // but if this is strictly for the "Tenant" entity (the owner), we can keep checks.
        // However, given the request "info of that user who loged in", we simply pass the user.

        return view('tenant.profile.edit', compact('user'));
    }

    /**
     * Update the tenant profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return redirect()->route('tenant.profile.edit')->with('success', 'Profile updated successfully.');
    }
}
