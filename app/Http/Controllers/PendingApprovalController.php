<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PendingApprovalController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Onboarding/PendingApproval', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'account_status' => $user->account_status,
                'account_type' => $user->account_type,
                'email_verified' => $user->hasVerifiedEmail(),
                'approved_at' => $user->approved_at,
            ],
        ]);
    }
}
