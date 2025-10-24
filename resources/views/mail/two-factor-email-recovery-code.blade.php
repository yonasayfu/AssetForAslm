@component('mail::message')
# Two-Factor Authentication Recovery Codes

Hello {{ $user->name }},

You are receiving this email because you requested your two-factor authentication email recovery codes.

Please keep these codes in a safe place. Each code can be used only once.

@foreach ($recoveryCodes as $code)
- {{ $code }}
@endforeach

If you did not request these codes, please contact support immediately.

Thanks,
{{ config('app.name') }}
@endcomponent