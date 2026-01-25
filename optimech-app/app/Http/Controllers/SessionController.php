<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function signIn(Request $request)
    {
        $loginAttr = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($loginAttr)) {
            $request->session()->regenerate();

            return redirect()->route('dashboard.index');
        } else {
            throw ValidationException::withMessages([
                'username' => 'Invalid Credentials',
            ]);
        }
    }

    // public function register()
    // {
    //     return view('auth.register');
    // }
    // public function store(Request $request)
    // {
    //     // return $request;

    //     $userAttributes = $request->validate([
    //         'name' => ['required', 'min:3', 'unique:users,name'],
    //         'email' => ['required','email'],
    //         'password' => ['required', 'confirmed', Password::min(6)]
    //     ]);

    //     $user = User::create($userAttributes);

    //     Auth::login($user);

    //     return redirect()->route('dashboard.index');
    // }

    public function logout()
    {
        Auth::logout();

        return redirect('/');

    }
}
