<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Lab404\Impersonate\Services\ImpersonateManager;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');
        $impersonateManager = app(ImpersonateManager::class);
        $isImpersonating = $impersonateManager->isImpersonating();
        $impersonator = $isImpersonating ? $impersonateManager->getImpersonator() : null;
        $impersonatedUser = $isImpersonating ? $request->user() : null;

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
                'roles' => $request->user()
                    ? $request->user()->getRoleNames()->values()->toArray()
                    : [],
                'permissions' => $request->user()
                    ? $request->user()->getAllPermissions()->pluck('name')->values()->toArray()
                    : [],
                'can' => [
                    'viewStaff' => $request->user()?->can('staff.view') ?? false,
                    'manageUsers' => $request->user()?->can('users.manage') ?? false,
                    'manageRoles' => $request->user()
                        ? ($request->user()->can('roles.manage') || $request->user()->can('users.manage'))
                        : false,
                ],
            ],
            'impersonation' => [
                'active' => $isImpersonating,
                'impersonator' => $impersonator ? [
                    'id' => $impersonator->getKey(),
                    'name' => $impersonator->name,
                    'email' => $impersonator->email,
                ] : null,
                'target' => $impersonatedUser ? [
                    'id' => $impersonatedUser->getKey(),
                    'name' => $impersonatedUser->name,
                    'email' => $impersonatedUser->email,
                ] : null,
            ],
            'navigation' => [
                'sidebar' => config('sidebar.groups', []),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
