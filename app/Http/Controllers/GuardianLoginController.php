<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GuardianLoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->hasRole('guardian')) {
            return redirect()->route('guardian.dashboard');
        }
        return view('guardian.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'school_code' => 'required|string',
            'phone'       => 'required|string',
            'password'    => 'required|string',
        ], [
            'school_code.required' => 'School code is required.',
            'phone.required'    => 'Phone number is required.',
            'password.required' => 'Password is required.',
        ]);

        $school = School::where('slug', Str::lower(trim($request->school_code)))->first();

        if (! $school) {
            throw ValidationException::withMessages([
                'school_code' => "We couldn't find a school with that code.",
            ]);
        }

        // Normalize phone: strip spaces/dashes so "0712 345 678" matches "0712345678"
        $phone = preg_replace('/[\s\-\(\)]+/', '', $request->phone);

        // Bypass BelongsToSchool scope — look up globally by phone, then
        // enforce the school-code match below. Without this, the lookup was
        // silently filtered by whatever tenant happened to already be bound.
        $user = User::withoutSchoolScope()->where('phone', $phone)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => 'The phone number or password is incorrect.',
            ]);
        }

        if (! $user->hasRole('guardian')) {
            throw ValidationException::withMessages([
                'phone' => 'This account is not registered as a parent/guardian.',
            ]);
        }

        if ((int) $user->school_id !== (int) $school->id) {
            throw ValidationException::withMessages([
                'school_code' => 'This account does not belong to this school.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();
        $request->session()->put('tenant_school_id', $school->id);

        return redirect()->route('guardian.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('guardian.login');
    }
}
