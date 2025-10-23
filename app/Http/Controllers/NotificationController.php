<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Return unread notifications for the authenticated user.
     */
    public function index(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'unread_count' => $user->unreadNotifications()->count(),
                'notifications' => $user->unreadNotifications()->take(10),
            ]);
        }

        return Inertia::render('Notifications/Index', [
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $user->unreadNotifications()->take(25),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(DatabaseNotification $notification): \Illuminate\Http\Response
    {
        abort_if($notification->notifiable_id !== Auth::id(), 403);

        $notification->markAsRead();

        return response()->noContent();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): \Illuminate\Http\Response
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->noContent();
    }
}
