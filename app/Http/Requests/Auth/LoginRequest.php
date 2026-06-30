<?php

namespace App\Http\Requests\Auth;

use App\Models\Guardian;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credential = trim($this->input('email'));
        $password   = $this->input('password');
        $isPhone    = ! str_contains($credential, '@');

        if ($isPhone) {
            $user = $this->findUserByPhone($credential);

            if (! $user || ! Hash::check($password, $user->password)) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }

            $this->enforceSubdomainSchoolMatch($user);
            Auth::login($user, $this->boolean('remember'));
        } else {
            // Bypass BelongsToSchool scope — we look up by email globally,
            // then enforce school match only when a real subdomain is in use.
            $user = User::withoutSchoolScope()->where('email', $credential)->first();

            if (! $user || ! Hash::check($password, $user->password)) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }

            $this->enforceSubdomainSchoolMatch($user);
            Auth::login($user, $this->boolean('remember'));
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * In production with real subdomains, block a user who tries to log in
     * at another school's URL.  When running on a plain IP/domain (local dev
     * or single-domain deploy) this check is skipped — the school is resolved
     * from the user's own school_id after login.
     */
    private function enforceSubdomainSchoolMatch(?User $user): void
    {
        if (! $user || $user->isSuperAdmin()) return;
        if (! $this->hasSubdomain()) return;
        if (! app()->bound('currentSchool')) return;
        if ($user->school_id === null) return;

        $school = app('currentSchool');

        if ((int) $user->school_id !== (int) $school->id) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'This account does not belong to this school.',
            ]);
        }
    }

    private function hasSubdomain(): bool
    {
        $host = $this->getHost();

        // IP addresses (127.0.0.1, ::1, etc.) are never subdomains
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return false;
        }

        $appDomain = config('tenancy.domain', '');
        if ($appDomain && str_ends_with($host, '.' . $appDomain)) {
            return true;
        }

        return count(explode('.', $host)) >= 3;
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email', '')) . '|' . $this->ip());
    }

    private function findUserByPhone(string $raw): ?User
    {
        $phone = preg_replace('/[\s\-\(\)]+/', '', $raw);

        $user = User::withoutSchoolScope()->where('phone', $phone)->first();
        if ($user) return $user;

        $staff = Staff::withoutSchoolScope()->where('phone', $phone)->whereNotNull('user_id')->first();
        if ($staff) return User::withoutSchoolScope()->find($staff->user_id);

        $guardian = Guardian::withoutSchoolScope()->where('phone', $phone)->whereNotNull('user_id')->first();
        if ($guardian) return User::withoutSchoolScope()->find($guardian->user_id);

        return null;
    }
}
