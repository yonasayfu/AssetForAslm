<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMailpitSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->headers->get('X-Mailpit-Token');
        $expected = config('mailbox.webhook_token');

        if (! $expected || ! hash_equals($expected, (string) $token)) {
            Log::warning('Mailpit webhook rejected due to invalid token.', [
                'ip' => $request->ip(),
            ]);

            abort(Response::HTTP_FORBIDDEN, 'Invalid Mailpit token.');
        }

        return $next($request);
    }
}
