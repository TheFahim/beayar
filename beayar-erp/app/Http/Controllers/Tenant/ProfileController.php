<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

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
            'avatar' => 'nullable|image|max:2048', // 2MB Max
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return redirect()->route('tenant.profile.show')->with('success', 'Profile updated successfully.');
    }

    /**
     * Send password change verification link.
     */
    public function sendPasswordChangeVerification(Request $request)
    {
        $user = Auth::user();

        // Generate a unique token for password change
        $token = Str::random(60);

        // Store the token in password_reset_tokens table with verification flag
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
                'verified' => false
            ]
        );

        // Send the email with the password change verification link
        $user->notify(new \App\Notifications\PasswordChangeNotification($token));

        return response()->json(['message' => 'Verification link sent successfully']);
    }

    /**
     * Check verification status (for polling)
     */
    public function checkVerificationStatus(Request $request)
    {
        $user = Auth::user();

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->where('verified', true)
            ->first();

        if ($resetRecord) {
            // For security, we need to generate a new token for the password change form
            $newToken = Str::random(60);

            // Update the record with the new token
            DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->update(['token' => Hash::make($newToken)]);

            return response()->json([
                'verified' => true,
                'token' => $newToken
            ]);
        }

        return response()->json(['verified' => false]);
    }

    /**
     * Verify email and mark as verified
     */
    public function verifyEmail(Request $request)
    {
        $token = $request->route('token');
        $email = $request->get('email');

        // Retrieve the token record
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord || !Hash::check($token, $resetRecord->token)) {
            return redirect()->route('tenant.profile.edit')
                ->with('error', 'Invalid or expired verification link.')
                ->with('verified', 'false');
        }

        // Generate a new token for the password change form
        $newToken = Str::random(60);

        // Mark as verified and update token
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->update([
                'verified' => true,
                'token' => Hash::make($newToken)
            ]);

        // Redirect back to profile edit with verification status
        return redirect()->route('tenant.profile.edit') . '?verified=true&token=' . urlencode($newToken);
    }

    /**
     * Show password change form.
     */
    public function showPasswordChangeForm(Request $request)
    {
        $token = $request->route('token');
        $email = $request->get('email');

        return view('tenant.profile.change-password', compact('token', 'email'));
    }

    /**
     * Handle password change submission.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Retrieve the token record
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid or expired password reset link.'], 422);
            }
            return back()->withErrors(['email' => 'Invalid or expired password reset link.']);
        }

        // Find the user
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'User not found.'], 422);
            }
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Update the password
        $user->forceFill([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ])->setRememberToken(Str::random(60));

        $user->save();

        // Delete the reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Trigger password reset event
        event(new PasswordReset($user));

        // Log out all other sessions except the current one
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
        }

        return redirect()->route('tenant.profile.edit')->with('success', 'Your password has been changed successfully.');
    }
}
