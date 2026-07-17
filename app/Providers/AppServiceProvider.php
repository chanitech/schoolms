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
        // Laravel's default pagination template targets Tailwind; this app
        // is AdminLTE/Bootstrap 4 — without this, the prev/next arrows
        // render as giant unstyled SVGs under every table.
        \Illuminate\Pagination\Paginator::useBootstrapFour();

        // Gate for super-admin menu visibility (used by AdminLTE `can` key)
        Gate::define('is-super-admin', fn ($user) => $user->isSuperAdmin());

        // AdminLTE's `can` menu key only checks Spatie PERMISSIONS via
        // Gate — it has no equivalent for ROLE-gated routes (Spatie doesn't
        // auto-register role names as Gate abilities the way it does
        // permissions). Routes gated by role: middleware need an explicit
        // Gate like this one so their menu item can actually be hidden from
        // users who'd otherwise see a link and 403 on click.
        Gate::define('is-treasurer', fn ($user) => $user->hasAnyRole(['treasurer', 'Admin']));

        // Matches the outer role gate on the whole 'treasurer.*' route
        // group (routes/web.php) — chief-accountant/accountant/treasurer
        // all sit in the staff-loan approval chain, plus Admin.
        Gate::define('is-loan-approver', fn ($user) => $user->hasAnyRole([
            'chief-accountant', 'accountant', 'treasurer', 'Admin',
        ]));

        // Membership in the Finance Office, independent of any specific
        // permission — TaskLogController@index and
        // FinanceDashboardController@myDashboard are deliberately
        // self-scoped (no cross-user data leak) so they were left without a
        // permission gate, but that also meant any role — including
        // Teacher — could reach them. This is the boundary for both those
        // routes and the 'Performance & Tasks' menu section.
        Gate::define('is-finance-office', fn ($user) => $user->hasAnyRole([
            'Principal', 'treasurer', 'chief-accountant', 'accountant', 'class_accountant',
            'procurement_officer', 'cashier', 'storekeeper', 'Admin',
        ]));

        // Matches TimetableController's own inline role check on
        // edit/update and TimetablePeriodController's constructor gate.
        Gate::define('is-timetable-admin', fn ($user) => $user->hasAnyRole(['Admin', 'Academic']));

        // Class coordinator: the staff member set as class_teacher of at
        // least one class (plus academic management). Controls who can mark
        // teacher attendance (attended/late/absent) on session logs and who
        // sees the Class Attendance page.
        Gate::define('is-class-coordinator', function ($user) {
            if ($user->hasAnyRole(['Admin', 'Academic', 'HOD', 'HR', 'Principal'])) {
                return true;
            }
            $staffId = $user->staff?->id;

            return $staffId
                && \App\Models\SchoolClass::where('class_teacher_id', $staffId)->exists();
        });

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
