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
            if ($this->isBypassable($request)) {
                return $next($request);
            }

            // Genuine unresolved-tenant state on a tenant-data route: do NOT
            // let this through unscoped — that's exactly what orphaned rows
            // with school_id=null before (see BelongsToSchool::bootBelongsToSchool).
            // Force back to a known state instead of serving/creating unscoped data.
            if (auth()->check()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'school_code' => 'Your school session could not be verified. Please log in again.',
                ]);
            }

            abort(403, 'No school context could be resolved for this request.');
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
        // 1. School explicitly selected via the School Code at login — the
        //    primary, reliable mechanism. Checked first: the app's own base
        //    domain (e.g. schoolms.chanitech.co.tz) is itself a 3-part host
        //    that the subdomain checks below would otherwise misread as a
        //    school slug ("schoolms"), fail to match, and — since those
        //    checks used to return immediately on a miss — never even reach
        //    this session value, always falling through to the hardcoded
        //    .env default instead.
        if ($id = session('tenant_school_id')) {
            $school = School::find((int) $id);
            if ($school) return $school;
        }

        // 2. Subdomain (only relevant if a school has its own real subdomain
        //    configured, distinct from the app's own base domain).
        $host      = $request->getHost();
        $appDomain = config('tenancy.domain', '');

        if ($appDomain && str_ends_with($host, '.' . $appDomain)) {
            $subdomain = substr($host, 0, strlen($host) - strlen('.' . $appDomain) - 1);
            if ($subdomain) {
                $school = School::where('slug', $subdomain)->first();
                if ($school) return $school;
            }
        }

        if (! filter_var($host, FILTER_VALIDATE_IP)) {
            $parts = explode('.', $host);
            if (count($parts) >= 3) {
                $school = School::where('slug', $parts[0])->first();
                if ($school) return $school;
            }
        }

        return null;
    }

    // Routes that legitimately have no resolved tenant yet: guest auth pages
    // (the School Code is validated inline by LoginRequest, not by this
    // middleware), the super-admin panel (operates across all schools by
    // design), guardian auth, the subscription-expired page, the root
    // redirect, and the framework health check.
    private function isBypassable(Request $request): bool
    {
        return $request->is(
            '/', 'up',
            'login', 'register', 'logout',
            'forgot-password', 'reset-password', 'reset-password/*',
            'confirm-password', 'verify-email', 'verify-email/*',
            'email/verification-notification', 'password',
            'guardian/*', 'super/*', 'subscription/*'
        );
    }
}
