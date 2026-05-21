<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\AdminImpersonation::class,
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\CheckBlockedUser::class,
           // \App\Http\Middleware\CheckRole::class,
        ]);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'impersonate' => \App\Http\Middleware\AdminImpersonation::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Redirect back with an error message instead of showing a blank 419 page
        $exceptions->render(function (TokenMismatchException $e, $request) {
            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation', 'amount_src', 'proof']))
                ->with('error', 'Your session expired. Please review your details and submit again.');
        });
    })
    ->withProviders([
        App\Providers\ScheduleServiceProvider::class, // Add this line
    ])->create();
