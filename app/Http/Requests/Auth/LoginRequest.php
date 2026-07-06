<?php

namespace App\Http\Requests\Auth;

use App\Models\Guardian;
use App\Models\School;
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
    // Set by authenticate() so the controller can read which school was
    // resolved from the entered code without a second lookup.
    public ?School $resolvedSchool = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_code' => ['required', 'string'],
            'email'       => ['required', 'string'],
            'password'    => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $schoolCode = Str::lower(trim($this->input('school_code')));
        $school     = School::where('slug', $schoolCode)->first();

        if (! $school) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'school_code' => "We couldn't find a school with that code.",
            ]);
        }

        $credential = trim($this->input('email'));
        $password   = $this->input('password');
        $isPhone    = ! str_contains($credential, '@');

        $user = $isPhone
            ? $this->findUserByPhone($credential)
            // Bypass BelongsToSchool scope — we look up by email globally,
            // then enforce the school-code match below.
            : User::withoutSchoolScope()->where('email', $credential)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $this->enforceSchoolCodeMatch($user, $school);

        $this->resolvedSchool = $school;
        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * The entered School Code is now the source of truth for which tenant
     * this login belongs to — reject if the account belongs to a different
     * school. Super admins bypass this so the code can be used to enter any
     * school's context directly.
     */
    private function enforceSchoolCodeMatch(User $user, School $school): void
    {
        if ($user->isSuperAdmin()) return;
        if ($user->school_id === null) return;

        if ((int) $user->school_id !== (int) $school->id) {
            throw ValidationException::withMessages([
                'school_code' => 'This account does not belong to this school.',
            ]);
        }
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
