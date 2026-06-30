<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'phone'    => 'required|string',
            'password' => 'required|string',
        ], [
            'phone.required'    => 'Phone number is required.',
            'password.required' => 'Password is required.',
        ]);

        // Normalize phone: strip spaces/dashes so "0712 345 678" matches "0712345678"
        $phone = preg_replace('/[\s\-\(\)]+/', '', $request->phone);

        $user = User::where('phone', $phone)->first();

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

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

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
