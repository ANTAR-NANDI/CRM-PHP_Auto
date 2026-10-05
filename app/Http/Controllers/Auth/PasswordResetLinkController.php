<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a six digit OTP to an active user instead of a reset link.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower((string) $request->input('email'));
        $user = User::query()->where('email', $email)->where('is_active', true)->first();

        if (! $user) {
            return back()->with('status', 'If an active account uses this email address, a reset code will be sent.');
        }

        $otp = (string) random_int(100000, 999999);
        DB::table('password_reset_otps')->where('email', $email)->delete();
        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Mail::to($user->email)->send(new PasswordResetOtpMail($otp));
        $request->session()->put('password_reset_email', $email);

        return redirect()->route('password.otp.form')->with('status', 'A six-digit reset code was sent to your email address.');
    }
}
