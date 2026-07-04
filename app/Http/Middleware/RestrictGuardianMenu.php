<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictGuardianMenu
{
    /**
     * Guardians must never see the staff/admin sidebar (school dashboard,
     * Treasurer Office, HR, Academic, etc.) — most of those menu items have
     * no permission gate at all, so the safest fix is to replace the whole
     * sidebar with a small guardian-only menu before any view renders.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->hasRole('guardian')) {
            config(['adminlte.menu' => [
                ['text' => 'My Children', 'route' => 'guardian.dashboard', 'icon' => 'fas fa-home'],
                ['text' => 'Fees & Payments', 'route' => 'guardian.fees', 'icon' => 'fas fa-wallet'],
            ]]);
        }

        return $next($request);
    }
}
