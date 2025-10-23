<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendVerificationCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PasswordResetMfaController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! $user->recovery_email) {
            return back()->with('status', 'If a matching account with a recovery email was found, a verification code has been sent.');
        }

        $code = random_int(100000, 999999);
        Cache::put('password_reset_code:'.$user->id, $code, now()->addMinutes(15));

        // Send email with the code to the user's recovery email.
        Mail::to($user->recovery_email)->send(new SendVerificationCode((string) $code));

        // Force a full page visit to the Enter Code page
        return Inertia::location(route('password.enter-code', [
            'email' => $request->email,
        ]));
    }

    /**
     * Verify the MFA code and create a password reset token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric|digits:6',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        $storedCode = Cache::get('password_reset_code:'.$user->id);

        if (! $storedCode || (int) $storedCode !== (int) $request->code) {
            throw ValidationException::withMessages([
                'code' => ['The verification code is invalid or has expired.'],
            ]);
        }

        // Clear the MFA code from cache
        Cache::forget('password_reset_code:'.$user->id);

        // Create a standard password reset token
        $token = Password::createToken($user);

        // Redirect to the final password reset form
        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }
}
