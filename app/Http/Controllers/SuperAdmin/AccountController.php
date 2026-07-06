<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AccountController extends Controller
{
    // Super admin sees every account, across every school, in one place.
    public function index(Request $request)
    {
        $query = User::withoutGlobalScope('school')->with(['school', 'roles']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($schoolId = $request->input('school_id')) {
            $query->where('school_id', $schoolId);
        }

        if ($role = $request->input('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        $users = $query->orderByDesc('id')->paginate(25)->withQueryString();

        $schools = School::orderBy('name')->get(['id', 'name']);
        $roles   = Role::orderBy('name')->pluck('name');

        return view('super_admin.accounts.index', compact('users', 'schools', 'roles'));
    }

    // Direct fix for a user assigned to the wrong school (the exact mismatch
    // this feature was built to resolve without needing tinker).
    //
    // Deliberately NOT using implicit Eloquent route binding (User $user)
    // here: User uses the BelongsToSchool scope, so implicit binding would
    // silently 404 whenever the target user belongs to a different school
    // than whatever the super admin's own session currently has bound as
    // currentSchool — exactly the cross-school case this action exists for.
    public function changeSchool(Request $request, int $user)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
        ]);

        $targetUser = User::withoutGlobalScope('school')->findOrFail($user);
        $targetUser->update(['school_id' => $request->school_id]);

        return back()->with('success', "{$targetUser->email} moved to " . School::find($request->school_id)->name . '.');
    }
}
