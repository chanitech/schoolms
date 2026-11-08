<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffMember
{
    // Many staff actions have no permission of their own and rely on login
    // alone, so parents and role-less accounts stop here.
    private const NON_STAFF_PATHS = [
        'dashboard', 'guardian', 'guardian/*', 'notifications', 'notifications/*',
        'notices', 'notices/*', 'marks/exams-by-session', 'profile', 'password',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $isStaff = $user->isSuperAdmin()
            || $user->roles->pluck('name')->contains(fn ($role) => $role !== 'guardian');

        if ($isStaff || $request->is(...self::NON_STAFF_PATHS)) {
            return $next($request);
        }

        abort(403, 'This area is for school staff only.');
    }
}
