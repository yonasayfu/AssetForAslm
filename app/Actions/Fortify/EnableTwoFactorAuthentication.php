<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Collection;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\RecoveryCode;

class EnableTwoFactorAuthentication
{
    public function __construct(
        protected TwoFactorAuthenticationProvider $provider
    ) {
    }

    /**
     * Enable two factor authentication for the given user.
     */
    public function __invoke(User $user, bool $force = false): void
    {
        if (! empty($user->two_factor_secret) && ! $force) {
            return;
        }

        $secretLength = (int) config('fortify-options.two-factor-authentication.secret-length', 16);

        $recoveryCodes = Collection::times(8, fn () => RecoveryCode::generate())->all();
        $emailRecoveryCodes = Collection::times(8, fn () => RecoveryCode::generate())->all();

        $payload = [
            'two_factor_secret' => Fortify::currentEncrypter()->encrypt(
                $this->provider->generateSecretKey($secretLength)
            ),
            'two_factor_email_recovery_codes' => $emailRecoveryCodes,
        ];

        try {
            $payload['two_factor_recovery_codes'] = Fortify::currentEncrypter()->encrypt(
                json_encode($recoveryCodes, JSON_THROW_ON_ERROR)
            );
        } catch (\JsonException) {
            $payload['two_factor_recovery_codes'] = Fortify::currentEncrypter()->encrypt(json_encode($recoveryCodes));
        }

        if (! Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm')) {
            $payload['two_factor_confirmed_at'] = now();
        }

        $user->forceFill($payload)->save();

        TwoFactorAuthenticationEnabled::dispatch($user);
    }
}
