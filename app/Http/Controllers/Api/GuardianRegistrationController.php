<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class GuardianRegistrationController extends Controller
{
    public function store(Request $request, string $schoolSlug): JsonResponse
    {
        $school = School::where('slug', $schoolSlug)->firstOrFail();

        $validated = $request->validate([
            'parentName'       => 'required|string|max:255',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'required|string|max:20',
            'childAdmissionNo' => 'required|string|max:50',
        ]);

        $phone = preg_replace('/[\s\-\(\)]+/', '', $validated['phone']);

        $student = Student::withoutSchoolScope()
            ->where('school_id', $school->id)
            ->where('admission_no', $validated['childAdmissionNo'])
            ->where('status', 'active')
            ->first();

        if (! $student) {
            throw ValidationException::withMessages([
                'childAdmissionNo' => 'No active student found with that admission number for this school.',
            ]);
        }

        [$firstName, $lastName] = $this->splitName($validated['parentName']);

        $user = User::withoutSchoolScope()
            ->where('school_id', $school->id)
            ->where('phone', $phone)
            ->first();

        $plainPassword = null;
        $createdNewAccount = false;

        if (! $user) {
            $plainPassword = $phone;
            $email = $validated['email'] ?? "guardian.{$phone}@parents.{$schoolSlug}.local";

            $user = User::create([
                'school_id'  => $school->id,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'name'       => trim("{$firstName} {$lastName}"),
                'email'      => $email,
                'phone'      => $phone,
                'password'   => Hash::make($plainPassword),
            ]);
            $createdNewAccount = true;
        }

        // Ensure the role is set even if a prior attempt created the user but
        // failed before assigning it (e.g. role didn't exist yet on this school).
        Role::firstOrCreate(['name' => 'guardian', 'guard_name' => 'web']);
        if (! $user->hasRole('guardian')) {
            $user->assignRole('guardian');
        }

        $guardian = Guardian::withoutSchoolScope()->where('user_id', $user->id)->first();

        if (! $guardian) {
            $guardian = Guardian::create([
                'school_id'            => $school->id,
                'user_id'              => $user->id,
                'first_name'           => $firstName,
                'last_name'            => $lastName,
                'relation_to_student'  => 'Parent/Guardian',
                'phone'                => $phone,
                'email'                => $validated['email'] ?? null,
            ]);
        }

        $student->guardian_id = $guardian->id;
        $student->save();

        Log::info('Guardian self-registration', [
            'school'        => $schoolSlug,
            'admission_no'  => $validated['childAdmissionNo'],
            'phone'         => $phone,
            'ip'            => $request->ip(),
            'new_account'   => $createdNewAccount,
        ]);

        return response()->json([
            'success'      => true,
            'new_account'  => $createdNewAccount,
            'login'        => $createdNewAccount
                ? ['phone' => $phone, 'password' => $plainPassword]
                : null,
            'message'      => $createdNewAccount
                ? 'Guardian account created and linked to the student.'
                : 'Existing guardian account linked to the student.',
        ]);
    }

    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [$parts[0], $parts[1] ?? '-'];
    }
}
