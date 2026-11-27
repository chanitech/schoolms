<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Tells the CHANITECH admin panel that somebody used ShulePRO today, so its Visitor Analytics can
 * show ShulePRO next to the website.
 *
 * Privacy: only an anonymous, salted hash of the visitor, a normalised page path, the role name and
 * the school's slug and name are sent. No names, emails, phone numbers or learner data. At most one
 * report per visitor per day, sent after the response has gone out, and any failure is ignored.
 * Does nothing until CHANITECH_ANALYTICS_URL and CHANITECH_ANALYTICS_TOKEN are set.
 */
class ReportVisitToChanitech
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $url = config('services.chanitech_analytics.url');
        $token = config('services.chanitech_analytics.token');

        if (! $url || ! $token || ! $this->worthReporting($request, $response)) {
            return;
        }

        $user = $request->user();
        $key = $user
            ? 'user:'.$user->getAuthIdentifier()
            : 'session:'.($request->hasSession() ? $request->session()->getId() : $request->ip());
        $salt = config('services.chanitech_analytics.salt') ?: config('app.key');
        $visitor = hash('sha256', 'shulepro|'.$key.'|'.$salt);

        // one report per visitor per day
        if (! Cache::add('chanitech-visit:'.$visitor.':'.now()->toDateString(), 1, now()->endOfDay())) {
            return;
        }

        $school = app()->bound('currentSchool') ? app('currentSchool') : null;

        try {
            Http::withToken($token)->acceptJson()->timeout(3)->post($url, [
                'source' => 'shulepro',
                'visitor_key' => $visitor,
                'path' => $this->normalisedPath($request),
                'role' => $user && method_exists($user, 'getRoleNames') ? $user->getRoleNames()->first() : null,
                'school_slug' => $school?->slug,
                'school_name' => $school?->name,
                'authenticated' => (bool) $user,
                'in_app' => str_contains((string) $request->userAgent(), '; wv)'),   // Android WebView = the ShulePRO app
            ]);
        } catch (Throwable) {
            // analytics must never affect ShulePRO
        }
    }

    private function worthReporting(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && $response->isSuccessful()
            && ! $request->ajax()
            && ! $request->expectsJson()
            && ! $request->is('up', 'livewire*', 'api/*', 'storage/*', 'build/*', 'css/*', 'js/*', 'images/*', 'vendor/*', 'favicon*', 'logout');
    }

    /** /students/123/edit -> /students/{id}/edit, no query string */
    private function normalisedPath(Request $request): string
    {
        $path = '/'.ltrim($request->path(), '/');
        $path = preg_replace('#/[0-9]+(?=/|$)#', '/{id}', $path) ?? $path;
        $path = preg_replace('#/[0-9a-f]{8}-[0-9a-f-]{27,}(?=/|$)#i', '/{id}', $path) ?? $path;

        return mb_substr($path, 0, 190);
    }
}
