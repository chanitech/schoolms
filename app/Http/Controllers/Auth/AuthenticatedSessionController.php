<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\School;
use Database\Seeders\DemoSchoolSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Cached briefly rather than queried on every page view — the demo
        // school's existence changes rarely, and this runs on the one page
        // every visitor (including prospects with no account yet) hits first.
        $demoAvailable = Cache::remember('demo-school-available', 3600, function () {
            return School::where('slug', DemoSchoolSeeder::SLUG)
                ->where('subscription_status', 'active')
                ->exists();
        });

        return view('auth.login', [
            'demoAvailable' => $demoAvailable,
            'demoSlug' => DemoSchoolSeeder::SLUG,
            'demoEmail' => DemoSchoolSeeder::LOGIN_EMAIL,
            'demoPassword' => DemoSchoolSeeder::LOGIN_PASSWORD,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Store the school resolved from the entered School Code in session
        // so ResolveTenant uses it on every subsequent request — no
        // subdomain needed, and this works even for super admins (whose own
        // school_id is null).
        if ($request->resolvedSchool) {
            $request->session()->put('tenant_school_id', $request->resolvedSchool->id);
        }

        if ($user->hasRole('guardian')) {
            return redirect()->intended(route('guardian.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        $isGuardian = $currentUser?->hasRole('guardian');

        Auth::guard('web')->logout();

        $request->session()->forget('tenant_school_id');
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $isGuardian
            ? redirect()->route('guardian.login')
            : redirect()->route('login');
    }
}
