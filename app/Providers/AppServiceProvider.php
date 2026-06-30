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

        // Share unread notification count with every view
        View::composer('*', function ($view) {
            if (Auth::check()) {
                /** @var \App\Models\User $u */
                $u = Auth::user();
                $view->with('_unreadNotifCount', $u->unreadNotifications()->count());
            } else {
                $view->with('_unreadNotifCount', 0);
            }
        });
    }
}
