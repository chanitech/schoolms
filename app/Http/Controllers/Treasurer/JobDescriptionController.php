<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\JobDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobDescriptionController extends Controller
{
    private const ROLES = [
        'treasurer', 'chief-accountant', 'accountant', 'class_accountant',
        'procurement_officer', 'cashier', 'storekeeper',
    ];

    public function __construct()
    {
        $this->middleware('permission:manage job descriptions');
    }

    public function index()
    {
        $existing = JobDescription::pluck('description', 'role_name');

        $descriptions = collect(self::ROLES)->map(fn ($role) => [
            'role_name' => $role,
            'description' => $existing[$role] ?? null,
        ]);

        return view('treasurer.job-descriptions.index', compact('descriptions'));
    }

    public function update(Request $request, string $roleName)
    {
        if (!in_array($roleName, self::ROLES, true)) {
            abort(404);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:5000',
        ]);

        JobDescription::updateOrCreate(
            ['role_name' => $roleName],
            ['description' => $validated['description'], 'updated_by' => Auth::id()]
        );

        return back()->with('success', 'Job description updated.');
    }
}
