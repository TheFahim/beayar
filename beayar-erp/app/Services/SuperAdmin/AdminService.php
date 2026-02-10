<?php

namespace App\Services\SuperAdmin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    public function authenticate(string $email, string $password): ?Admin
    {
        $admin = Admin::where('email', $email)->first();

        if ($admin && Hash::check($password, $admin->password)) {
            return $admin;
        }

        return null;
    }

    public function impersonateTenant(User $user)
    {
        $adminId = Auth::guard('admin')->id() ?? Auth::id();
        Auth::login($user);
        session()->put('impersonated_by_admin_id', $adminId);
    }

    public function stopImpersonation()
    {
        Auth::logout();
        if (session()->has('impersonated_by_admin_id')) {
            // Logic to restore admin session if needed, though usually admin and web are different guards
            session()->forget('impersonated_by_admin_id');
        }
    }
}
