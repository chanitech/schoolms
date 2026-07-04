<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Gate for super-admin menu visibility (used by AdminLTE `can` key)
        Gate::define('is-super-admin', fn ($user) => $user->isSuperAdmin());

        // Share unread notification count with every view.
        // View::composer('*') fires once per rendered view/partial, not once
        // per page — a single AdminLTE page composes 20-30+ partials (sidebar,
        // navbar, footer, etc.), so without memoizing this ran the same COUNT
        // query 100+ times per request. Cache it once per request instead.
        View::composer('*', function ($view) {
            static $count = null;
            static $userId = null;

            $currentUserId = Auth::id();

            if ($count === null || $userId !== $currentUserId) {
                $count = Auth::check() ? Auth::user()->unreadNotifications()->count() : 0;
                $userId = $currentUserId;
            }

            $view->with('_unreadNotifCount', $count);
        });
    }
}
