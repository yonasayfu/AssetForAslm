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
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(15)
            ->through(function (DatabaseNotification $notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => optional($notification->read_at)->toIso8601String(),
                    'created_at' => optional($notification->created_at)->toIso8601String(),
                ];
            });

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Return unread notifications for the authenticated user.
     */
    public function getUnread(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $unread = $user->unreadNotifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(function (DatabaseNotification $notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => optional($notification->read_at)->toIso8601String(),
                    'created_at' => optional($notification->created_at)->toIso8601String(),
                ];
            });

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $unread,
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
