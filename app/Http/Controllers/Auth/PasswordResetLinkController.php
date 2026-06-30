<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
        ], [
            'login.required' => 'Please enter your email address or phone number.',
        ]);

        $login = trim($request->input('login'));
        $email = str_contains($login, '@') ? $login : $this->resolveEmailFromPhone($login);

        if (! $email) {
            return back()->withInput()->withErrors([
                'login' => 'No account found with that phone number. Please contact the administrator.',
            ]);
        }

        // Temporarily remove the school scope so the password broker can find
        // users from any school — this is an unauthenticated route.
        $boundSchool = app()->bound('currentSchool') ? app('currentSchool') : null;
        app()->forgetInstance('currentSchool');

        $status = Password::sendResetLink(['email' => $email]);

        if ($boundSchool) app()->instance('currentSchool', $boundSchool);

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', 'Password reset link has been sent to your email address.')
            : back()->withInput()->withErrors(['login' => __($status)]);
    }

    private function resolveEmailFromPhone(string $raw): ?string
    {
        $phone = preg_replace('/[\s\-\(\)]+/', '', $raw);

        $user = User::withoutSchoolScope()->where('phone', $phone)->whereNotNull('email')->first();
        if ($user) return $user->email;

        $staff = Staff::withoutSchoolScope()->where('phone', $phone)->whereNotNull('user_id')->first();
        if ($staff) {
            $user = User::withoutSchoolScope()->where('id', $staff->user_id)->whereNotNull('email')->first();
            if ($user) return $user->email;
        }

        $guardian = Guardian::withoutSchoolScope()->where('phone', $phone)->whereNotNull('user_id')->first();
        if ($guardian) {
            $user = User::withoutSchoolScope()->where('id', $guardian->user_id)->whereNotNull('email')->first();
            if ($user) return $user->email;
        }

        return null;
    }
}
