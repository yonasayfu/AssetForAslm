<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorEmailRecoveryCodeMailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class TwoFactorEmailRecoveryController extends Controller
{
    public function sendRecoveryCode(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->two_factor_email_recovery_codes || ! $user->recovery_email) {
            throw ValidationException::withMessages([
                'email' => [__('You do not have a recovery email or email recovery codes set up.')],
            ]);
        }

        Mail::to($user->recovery_email)->send(new TwoFactorEmailRecoveryCodeMailable($user, $user->two_factor_email_recovery_codes));

        return response()->json(['message' => 'Recovery codes sent to your recovery email.']);
    }

    public function verifyRecoveryCode(Request $request)
    {
        $request->validate([
            'recovery_code' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $user || ! $user->two_factor_email_recovery_codes) {
            throw ValidationException::withMessages([
                'recovery_code' => [__('Invalid recovery code.')],
            ]);
        }

        $recoveryCodes = collect($user->two_factor_email_recovery_codes);

        if (! $recoveryCodes->contains($request->recovery_code)) {
            throw ValidationException::withMessages([
                'recovery_code' => [__('Invalid recovery code.')],
            ]);
        }

        // Mark the used recovery code as consumed
        $user->forceFill([
            'two_factor_email_recovery_codes' => $recoveryCodes->reject(fn ($code) => $code === $request->recovery_code)->values()->all(),
        ])->save();

        // Log the user in
        auth()->login($user);

        return response()->noContent();
    }
}
