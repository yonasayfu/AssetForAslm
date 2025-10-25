<?php

use App\Console\Commands\MakeModule;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Jobs\Mailbox\PurgeExpiredMailboxMessages;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'mailpit.signature' => \App\Http\Middleware\VerifyMailpitSignature::class,
            'approved' => \App\Http\Middleware\EnsureUserIsApproved::class,
        ]);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withBroadcasting(__DIR__.'/../routes/channels.php')
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new PurgeExpiredMailboxMessages(), env('MAILBOX_QUEUE', 'default'))
            ->dailyAt('02:00');

        $schedule->command('mailpit:sync --limit=100')
            ->hourly()
            ->withoutOverlapping();
    })
    ->withCommands([
        MakeModule::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
