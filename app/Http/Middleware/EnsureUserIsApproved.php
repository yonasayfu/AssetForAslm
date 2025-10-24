<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->account_status !== User::STATUS_ACTIVE) {
            return redirect()
                ->route('onboarding.pending')
                ->with('status', $user->account_status);
        }

        return $next($request);
    }
}
