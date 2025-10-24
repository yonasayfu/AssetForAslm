<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ActivityLog::class);

        $activityLogs = ActivityLog::with('causer:id,name,email')
            ->latest()
            ->paginate(10);

        return Inertia::render('ActivityLogs/Index', [
            'activityLogs' => $activityLogs,
            'breadcrumbs' => [
                ['title' => 'Activity Logs', 'href' => route('activity-logs.index')],
            ],
        ]);
    }
}
