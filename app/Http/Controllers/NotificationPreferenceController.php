<?php

namespace App\Http\Controllers;

use App\Models\UserNotificationPreference;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationPreferenceController extends Controller
{
    // Define available notification types and channels
    protected array $availableNotificationTypes = [
        'NewAssignmentNotification',
        'DataExportReadyNotification',
        // Add other notification types here
    ];

    public function index(Request $request): Response
    {
        $user = $request->user();

        $preferences = $user->notificationPreferences()->get();

        return Inertia::render('Profile/NotificationPreferences', [
            'preferences' => $preferences,
            'availableNotificationTypes' => $this->availableNotificationTypes,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'notification_type' => ['required', 'string', 'in:' . implode(',', $this->availableNotificationTypes)],
            'channel' => ['required', 'string', 'in:mail,database'], // Assuming these are the only channels
            'enabled' => ['required', 'boolean'],
        ]);

        $user = $request->user();

        UserNotificationPreference::updateOrCreate(
            [
                'user_id' => $user->id,
                'notification_type' => $request->notification_type,
                'channel' => $request->channel,
            ],
            [
                'enabled' => $request->enabled,
            ]
        );

        return response()->noContent();
    }
}
