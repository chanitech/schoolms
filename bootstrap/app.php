<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule; // ✅ add this import

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register in the web group and set priority: after StartSession (so session
        // is loaded before we read/write tenant_school_id) but before
        // SubstituteBindings (so route model binding is school-scoped).
        $middleware->appendToGroup('web', \App\Http\Middleware\ResolveTenant::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\RestrictGuardianMenu::class);
        $middleware->appendToPriorityList(
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\ResolveTenant::class,
        );

        // Register Spatie Laravel Permission middleware aliases
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'super_admin'        => \App\Http\Middleware\EnsureSuperAdmin::class,
            'api.key'            => \App\Http\Middleware\EnsureApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Redirect back gracefully instead of showing a raw 419 page
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please refresh and try again.'], 419);
            }

            $authRoutes = ['login', 'password.email', 'password.store', 'guardian.login.post'];
            $isAuthRoute = in_array($request->route()?->getName(), $authRoutes);

            return ($isAuthRoute
                ? redirect()->route('login')
                : redirect()->back()->withInput($request->except('password', 'password_confirmation', 'current_password'))
            )->with('error', 'Your session expired. Please try again.');
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Mark overdue loan repayments daily at midnight
        $schedule->command('loans:mark-overdue')->dailyAt('00:00');
    })
    ->create();