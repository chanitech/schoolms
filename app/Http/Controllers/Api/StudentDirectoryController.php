<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;

class StudentDirectoryController extends Controller
{
    public function index(string $schoolSlug): JsonResponse
    {
        $school = School::resolveBySlug($schoolSlug);

        $data = SchoolClass::withoutSchoolScope()
            ->where('school_id', $school->id)
            ->with(['students' => fn ($q) => $q->withoutGlobalScope('school')->where('status', 'active')])
            ->get()
            ->mapWithKeys(fn (SchoolClass $class) => [
                $class->name => $class->students
                    ->map(fn ($s) => [
                        'name'         => implode(' ', array_filter([$s->first_name, $s->middle_name, $s->last_name])),
                        'admission_no' => $s->admission_no,
                    ])
                    ->sortBy('name')
                    ->values(),
            ]);

        return response()->json($data);
    }
}
