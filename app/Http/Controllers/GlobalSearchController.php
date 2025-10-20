<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        if (! $request->user()?->can('users.manage') && ! $request->user()?->can('staff.view')) {
            abort(403);
        }

        $term = trim((string) $request->query('q', ''));

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $results = collect();

        if ($request->user()->can('users.manage')) {
            $userMatches = User::query()
                ->where(function ($query) use ($term) {
                    $query
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                })
                ->orderBy('name')
                ->limit(5)
                ->get();

            $results = $results->merge($userMatches->map(function (User $user) {
                return [
                    'type' => 'User',
                    'category' => 'Identity',
                    'title' => $user->name,
                    'description' => $user->email,
                    'url' => route('users.edit', $user),
                    'icon' => 'user',
                ];
            }));
        }

        if ($request->user()->can('staff.view')) {
            $staffMatches = Staff::query()
                ->where(function ($query) use ($term) {
                    $query
                        ->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('job_title', 'like', "%{$term}%");
                })
                ->orderBy('last_name')
                ->limit(5)
                ->get();

            $results = $results->merge($staffMatches->map(function (Staff $staff) {
                return [
                    'type' => 'Staff',
                    'category' => 'Directory',
                    'title' => $staff->full_name,
                    'description' => $staff->email,
                    'url' => route('staff.edit', $staff),
                    'icon' => 'users',
                ];
            }));
        }

        if ($results->isEmpty()) {
            $results->push([
                'type' => 'Assets',
                'category' => 'Coming Soon',
                'title' => 'Asset search will arrive with Phase 7 modules.',
                'description' => 'Use the Assets module filters once the domain modules are in place.',
                'url' => route('dashboard'),
                'icon' => 'package',
            ]);
        }

        return response()->json($results->take(15)->values());
    }
}


