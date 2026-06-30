<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in as a super admin to access this area.');
        }

        if (!$user->isSuperAdmin()) {
            return response()->view('errors.not-super-admin', ['user' => $user], 403);
        }

        return $next($request);
    }
}
