<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    // Routes that should work without a resolved school (landing, health, etc.)
    private const BYPASS_ROUTES = ['subscription.expired'];

    public function handle(Request $request, Closure $next): Response
    {
        $school = $this->resolveSchool($request);

        if (!$school) {
            // In local dev without subdomains, fall back to .env TENANT_SCHOOL_ID
            $fallbackId = config('tenancy.default_school_id');
            if ($fallbackId) {
                $school = School::find($fallbackId);
            }
        }

        if (!$school) {
            // No tenant resolved — allow the request through unscoped
            // (handles the case of the very first setup, or public landing pages)
            return $next($request);
        }

        // Gate: expired/cancelled subscriptions
        if (!$school->isActive()) {
            $routeName = $request->route()?->getName();
            $isSafeRoute = in_array($routeName, self::BYPASS_ROUTES)
                || $request->is('login', 'logout', 'subscription*');

            if (!$isSafeRoute && !$request->expectsJson()) {
                return redirect()->route('subscription.expired');
            }
        }

        // Bind the resolved school into the service container so the
        // BelongsToSchool global scope can read it on every model query.
        app()->instance('currentSchool', $school);

        return $next($request);
    }

    private function resolveSchool(Request $request): ?School
    {
        // 1. Subdomain (production)
        $host      = $request->getHost();
        $appDomain = config('tenancy.domain', '');

        if ($appDomain && str_ends_with($host, '.' . $appDomain)) {
            $subdomain = substr($host, 0, strlen($host) - strlen('.' . $appDomain) - 1);
            if ($subdomain) {
                return School::where('slug', $subdomain)->first();
            }
        }

        if (! filter_var($host, FILTER_VALIDATE_IP)) {
            $parts = explode('.', $host);
            if (count($parts) >= 3) {
                return School::where('slug', $parts[0])->first();
            }
        }

        // 2. School stored in session after login (set from user->school_id in
        //    AuthenticatedSessionController::store).
        if ($id = session('tenant_school_id')) {
            return School::find((int) $id);
        }

        return null;
    }
}
