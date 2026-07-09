<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SchoolController extends Controller
{
    public function index()
    {
        // Super admin sees ALL schools — no tenant scope
        $schools = School::withCount(['users', 'students', 'staff'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('super_admin.schools.index', compact('schools'));
    }

    public function create()
    {
        return view('super_admin.schools.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                    => 'required|string|max:255',
            'slug'                    => 'required|string|max:60|unique:schools,slug|alpha_dash',
            'email'                   => 'nullable|email|max:255',
            'phone'                   => 'nullable|string|max:30',
            'address'                 => 'nullable|string|max:255',
            'motto'                   => 'nullable|string|max:255',
            'website'                 => 'nullable|url|max:255',
            'plan'                    => 'required|in:basic,pro',
            'subscription_status'     => 'required|in:active,trial,expired,cancelled',
            'subscription_expires_at' => 'nullable|date',
            'logo'                    => 'nullable|image|max:2048',

            // First admin user
            'admin_first_name' => 'required|string|max:100',
            'admin_last_name'  => 'required|string|max:100',
            'admin_email'      => 'required|email|max:255|unique:users,email',
            'admin_password'   => 'required|string|min:8|confirmed',
        ]);

        // 1. Create the school (logo, if any, is stored right after since the
        // path needs the school's own id, which doesn't exist yet here).
        $school = School::create([
            'name'                    => $request->name,
            'slug'                    => Str::lower($request->slug),
            'email'                   => $request->email,
            'phone'                   => $request->phone,
            'address'                 => $request->address,
            'motto'                   => $request->motto,
            'website'                 => $request->website,
            'plan'                    => $request->plan,
            'subscription_status'     => $request->subscription_status,
            'subscription_expires_at' => $request->subscription_expires_at,
        ]);

        if ($request->hasFile('logo')) {
            $school->update([
                'logo' => $request->file('logo')->store("schools/{$school->id}/logos", 'public'),
            ]);
        }

        // 2. Also create a school_infos record so SchoolInfo::first() works for this tenant
        \DB::table('school_infos')->insert([
            'school_id'  => $school->id,
            'name'       => $school->name,
            'motto'      => $school->motto,
            'email'      => $school->email,
            'phone'      => $school->phone,
            'address'    => $school->address,
            'website'    => $school->website,
            'lock_results_for_guardians' => true,
            'lock_results_only_overdue'  => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create the first admin user for this school
        $adminUser = User::create([
            'school_id'      => $school->id,
            'name'           => $request->admin_first_name . ' ' . $request->admin_last_name,
            'first_name'     => $request->admin_first_name,
            'last_name'      => $request->admin_last_name,
            'email'          => $request->admin_email,
            'password'       => Hash::make($request->admin_password),
            'is_super_admin' => false,
        ]);

        // 4. Assign Admin role
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminUser->assignRole($adminRole);

        return redirect()->route('super.schools.show', $school->id)
            ->with('success', "School \"{$school->name}\" created. Admin: {$request->admin_first_name} {$request->admin_last_name} ({$request->admin_email}).");
    }

    public function show(School $school)
    {
        $school->loadCount(['users', 'students', 'staff']);
        $admins = User::withoutGlobalScope('school')
            ->where('school_id', $school->id)
            ->whereHas('roles', fn($q) => $q->where('name', 'Admin'))
            ->get(['id', 'name', 'email', 'created_at']);

        return view('super_admin.schools.show', compact('school', 'admins'));
    }

    public function edit(School $school)
    {
        return view('super_admin.schools.edit', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $request->validate([
            'name'                    => 'required|string|max:255',
            'slug'                    => 'required|string|max:60|alpha_dash|unique:schools,slug,' . $school->id,
            'email'                   => 'nullable|email|max:255',
            'phone'                   => 'nullable|string|max:30',
            'address'                 => 'nullable|string|max:255',
            'motto'                   => 'nullable|string|max:255',
            'website'                 => 'nullable|url|max:255',
            'plan'                    => 'required|in:basic,pro',
            'subscription_status'     => 'required|in:active,trial,expired,cancelled',
            'subscription_expires_at' => 'nullable|date',
            'logo'                    => 'nullable|image|max:2048',
        ]);

        $data = $request->only([
            'name', 'slug', 'email', 'phone', 'address',
            'motto', 'website', 'plan',
            'subscription_status', 'subscription_expires_at',
        ]);
        $data['slug'] = Str::lower($data['slug']);

        if ($request->hasFile('logo')) {
            if ($school->logo) Storage::disk('public')->delete($school->logo);
            $data['logo'] = $request->file('logo')->store("schools/{$school->id}/logos", 'public');
        }

        $school->update($data);

        // Sync school_infos
        \DB::table('school_infos')->where('school_id', $school->id)->update([
            'name'       => $school->name,
            'motto'      => $school->motto,
            'email'      => $school->email,
            'phone'      => $school->phone,
            'address'    => $school->address,
            'website'    => $school->website,
            'updated_at' => now(),
        ]);

        return redirect()->route('super.schools.show', $school->id)
            ->with('success', "School updated successfully.");
    }

    public function destroy(School $school)
    {
        // Safety: refuse to delete if it has students
        if ($school->students()->withoutGlobalScope('school')->count() > 0) {
            return back()->with('error', 'Cannot delete a school that has students. Deactivate it instead.');
        }
        $school->delete();
        return redirect()->route('super.schools.index')
            ->with('success', "School deleted.");
    }

    public function renewSubscription(Request $request, School $school)
    {
        $request->validate([
            'months' => 'required_without:custom_date|nullable|integer|min:1|max:120',
            'custom_date' => 'required_without:months|nullable|date|after:today',
        ]);

        if ($request->filled('custom_date')) {
            $newExpiry = \Carbon\Carbon::parse($request->custom_date)->endOfDay();
        } else {
            // Extend from today or current expiry, whichever is later
            $base = ($school->subscription_expires_at && $school->subscription_expires_at->isFuture())
                ? $school->subscription_expires_at
                : now();
            $newExpiry = $base->addMonths((int) $request->months)->endOfDay();
        }

        $school->update([
            'subscription_status'     => 'active',
            'subscription_expires_at' => $newExpiry,
        ]);

        return back()->with('success', "Subscription extended to {$newExpiry->format('d M Y')}.");
    }

    public function setSubscriptionStatus(Request $request, School $school)
    {
        $request->validate([
            'status' => 'required|in:active,trial,expired,cancelled',
        ]);

        $school->update(['subscription_status' => $request->status]);

        return back()->with('success', "Subscription status set to " . ucfirst($request->status) . ".");
    }

    // Deliberately NOT using implicit Eloquent route binding (User $user)
    // here: User uses the BelongsToSchool scope, so implicit binding would
    // silently 404 whenever $school isn't whatever the super admin's own
    // session currently has bound as currentSchool — i.e. almost always,
    // since a super admin only has one school in session at a time.
    public function resetUserPassword(Request $request, School $school, int $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $targetUser = User::withoutGlobalScope('school')->findOrFail($user);

        // Safety: user must actually belong to this school
        abort_if((int) $targetUser->school_id !== (int) $school->id, 403);

        $targetUser->update(['password' => Hash::make($request->password)]);

        return back()->with('success', "Password reset for {$targetUser->email}.");
    }

    // Add an extra admin user to an existing school
    public function addUser(Request $request, School $school)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
            'role'       => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'school_id'  => $school->id,
            'name'       => $request->first_name . ' ' . $request->last_name,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
        ]);
        $user->assignRole($request->role);

        return back()->with('success', "User {$request->email} added as {$request->role}.");
    }

}
