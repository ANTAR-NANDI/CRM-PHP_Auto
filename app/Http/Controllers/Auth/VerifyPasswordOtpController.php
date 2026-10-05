<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class VerifyPasswordOtpController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-password-otp', ['email' => $request->session()->get('password_reset_email')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $email = (string) $request->session()->get('password_reset_email');

        if ($email === '') {
            return redirect()->route('password.request');
        }

        $data = $request->validate(['otp' => ['required', 'digits:6']]);
        $record = DB::table('password_reset_otps')->where('email', $email)->latest('id')->first();

        if (! $record || now()->greaterThan($record->expires_at) || $record->attempts >= 5) {
            return back()->withErrors(['otp' => 'This code has expired or is no longer valid. Request a new one.']);
        }

        if (! Hash::check($data['otp'], $record->otp_hash)) {
            DB::table('password_reset_otps')->where('id', $record->id)->increment('attempts');

            return back()->withErrors(['otp' => 'The code is incorrect. Please try again.']);
        }

        DB::table('password_reset_otps')->where('email', $email)->delete();
        $request->session()->put('password_reset_verified_email', $email);
        $request->session()->put('password_reset_verified_at', now()->timestamp);
        $request->session()->forget('password_reset_email');

        return redirect()->route('password.reset');
    }
}
