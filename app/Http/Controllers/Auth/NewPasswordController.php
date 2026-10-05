<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view after a valid OTP was entered.
     */
    public function create(Request $request): View
    {
        if (! $request->session()->has('password_reset_verified_email') || now()->timestamp - (int) $request->session()->get('password_reset_verified_at', 0) > 900) {
            return redirect()->route('password.request')->withErrors(['email' => 'Verify a new reset code before changing your password.']);
        }

        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Store a new password after OTP verification.
     */
    public function store(Request $request): RedirectResponse
    {
        $verifiedEmail = (string) $request->session()->get('password_reset_verified_email');
        if ($verifiedEmail === '' || now()->timestamp - (int) $request->session()->get('password_reset_verified_at', 0) > 900) {
            return redirect()->route('password.request')->withErrors(['email' => 'Your verification has expired. Request a new code.']);
        }

        $request->validate(['password' => ['required', 'confirmed', Rules\Password::defaults()]]);

        $user = User::query()->where('email', $verifiedEmail)->where('is_active', true)->firstOrFail();
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();
        event(new PasswordReset($user));
        $request->session()->forget(['password_reset_verified_email', 'password_reset_verified_at']);

        return redirect()->route('login')->with('status', 'Password reset successfully. You can now sign in.');
    }
}
