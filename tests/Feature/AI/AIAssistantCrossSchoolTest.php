<?php

namespace Tests\Feature\AI;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Exam;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\AIAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Assert that every AIAssistantService tool only surfaces data belonging
 * to the currently-bound school (the tenant).  Each test:
 *  1. Creates data for two schools (A and B).
 *  2. Binds school A as the active tenant.
 *  3. Calls the tool under test.
 *  4. Asserts the result contains school-A data and does NOT contain
 *     school-B data.
 *
 * The raw DB::table() queries inside the service are the main risk —
 * they bypass the BelongsToSchool global scope.
 */
class AIAssistantCrossSchoolTest extends TestCase
{
    use RefreshDatabase;

    private School $schoolA;
    private School $schoolB;
    private AIAssistantService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable the global scope while seeding both schools
        app()->forgetInstance('currentSchool');

        $this->schoolA = School::create([
            'name' => 'School Alpha', 'slug' => 'alpha',
            'subscription_status' => 'active', 'plan' => 'pro',
        ]);

        $this->schoolB = School::create([
            'name' => 'School Beta', 'slug' => 'beta',
            'subscription_status' => 'active', 'plan' => 'pro',
        ]);

        $this->seedSchoolA();
        $this->seedSchoolB();

        $this->service = new AIAssistantService();
    }

    protected function tearDown(): void
    {
        app()->forgetInstance('currentSchool');
        parent::tearDown();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /** Bind a school as the active tenant for the duration of a callback. */
    private function asSchool(School $school, callable $fn): mixed
    {
        app()->instance('currentSchool', $school);
        try {
            return $fn();
        } finally {
            app()->forgetInstance('currentSchool');
        }
    }

    /** Invoke a protected method on the service via reflection. */
    private function invoke(string $method, mixed ...$args): mixed
    {
        $ref = new \ReflectionMethod(AIAssistantService::class, $method);
        return $ref->invoke($this->service, ...$args);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Fixtures
    // ─────────────────────────────────────────────────────────────────────────

    private function seedSchoolA(): void
    {
        $sid = $this->schoolA->id;

        $session = AcademicSession::create([
            'school_id' => $sid, 'name' => 'Alpha Session 2024', 'is_current' => true,
            'start_date' => '2024-01-01', 'end_date' => '2024-12-31',
        ]);

        $class = SchoolClass::create([
            'school_id' => $sid, 'name' => 'Alpha Form 1',
            'level' => 1, 'capacity' => 40,
        ]);

        $dept = Department::create(['school_id' => $sid, 'name' => 'Alpha Science Dept']);

        $subject = Subject::create([
            'school_id' => $sid, 'name' => 'Alpha Physics',
            'code' => 'APHY', 'type' => 'compulsory',
        ]);

        $exam = Exam::create([
            'school_id' => $sid, 'name' => 'Alpha Midterm',
            'term' => 1, 'academic_session_id' => $session->id,
        ]);

        $student = DB::table('students')->insertGetId([
            'school_id'    => $sid,
            'admission_no' => 'ALPHA001',
            'first_name'   => 'AlphaFirst',
            'last_name'    => 'AlphaLast',
            'gender'       => 'male',
            'date_of_birth'=> '2005-01-01',
            'class_id'     => $class->id,
            'status'       => 'active',
            'academic_session_id' => $session->id,
            'admission_date' => now(),
            'created_at'   => now(), 'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'school_id'  => $sid,
            'name'       => 'AlphaStaff Teacher',
            'first_name' => 'AlphaStaff',
            'last_name'  => 'Teacher',
            'email'      => 'alpha.teacher@school.test',
            'password'   => bcrypt('password'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $staff = DB::table('staff')->insertGetId([
            'school_id'     => $sid,
            'user_id'       => $userId,
            'first_name'    => 'AlphaStaff',
            'last_name'     => 'Teacher',
            'email'         => 'alpha.teacher@school.test',
            'department_id' => $dept->id,
            'role'          => 'Staff',
            'created_at'    => now(), 'updated_at' => now(),
        ]);

        DB::table('marks')->insert([
            'school_id'  => $sid,
            'student_id'         => $student,
            'subject_id'         => $subject->id,
            'exam_id'            => $exam->id,
            'academic_session_id'=> $session->id,
            'mark'               => 88,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('subject_class')->insert([
            'subject_id' => $subject->id,
            'class_id'   => $class->id,
            'teacher_id' => $staff,
        ]);
    }

    private function seedSchoolB(): void
    {
        $sid = $this->schoolB->id;

        $session = AcademicSession::create([
            'school_id' => $sid, 'name' => 'Beta Session 2024', 'is_current' => false,
            'start_date' => '2024-01-01', 'end_date' => '2024-12-31',
        ]);

        $class = SchoolClass::create([
            'school_id' => $sid, 'name' => 'Beta Form 1',
            'level' => 1, 'capacity' => 40,
        ]);

        $dept = Department::create(['school_id' => $sid, 'name' => 'Beta Science Dept']);

        $subject = Subject::create([
            'school_id' => $sid, 'name' => 'Beta Chemistry',
            'code' => 'BCHEM', 'type' => 'compulsory',
        ]);

        $exam = Exam::create([
            'school_id' => $sid, 'name' => 'Beta Midterm',
            'term' => 1, 'academic_session_id' => $session->id,
        ]);

        $student = DB::table('students')->insertGetId([
            'school_id'    => $sid,
            'admission_no' => 'BETA001',
            'first_name'   => 'BetaFirst',
            'last_name'    => 'BetaLast',
            'gender'       => 'female',
            'date_of_birth'=> '2005-06-15',
            'class_id'     => $class->id,
            'status'       => 'active',
            'academic_session_id' => $session->id,
            'admission_date' => now(),
            'created_at'   => now(), 'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'school_id'  => $sid,
            'name'       => 'BetaStaff Teacher',
            'first_name' => 'BetaStaff',
            'last_name'  => 'Teacher',
            'email'      => 'beta.teacher@school.test',
            'password'   => bcrypt('password'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $staff = DB::table('staff')->insertGetId([
            'school_id'     => $sid,
            'user_id'       => $userId,
            'first_name'    => 'BetaStaff',
            'last_name'     => 'Teacher',
            'email'         => 'beta.teacher@school.test',
            'department_id' => $dept->id,
            'role'          => 'Staff',
            'created_at'    => now(), 'updated_at' => now(),
        ]);

        DB::table('marks')->insert([
            'school_id'  => $sid,
            'student_id'         => $student,
            'subject_id'         => $subject->id,
            'exam_id'            => $exam->id,
            'academic_session_id'=> $session->id,
            'mark'               => 77,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('subject_class')->insert([
            'subject_id' => $subject->id,
            'class_id'   => $class->id,
            'teacher_id' => $staff,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tests — model-scoped tools
    // ─────────────────────────────────────────────────────────────────────────

    public function test_search_student_only_returns_current_school_student(): void
    {
        $result = $this->asSchool($this->schoolA, fn() =>
            $this->invoke('searchStudent', 'AlphaFirst')
        );

        $this->assertStringContainsString('AlphaFirst', $result['_exact_summary']);
        $this->assertStringNotContainsString('BetaFirst', $result['_exact_summary']);
    }

    public function test_search_student_does_not_find_other_school_student(): void
    {
        $result = $this->asSchool($this->schoolA, fn() =>
            $this->invoke('searchStudent', 'BetaFirst')
        );

        // The "not found" message echoes the query term — check admission no and found flag instead
        $this->assertStringContainsString('No student found', $result['_exact_summary']);
        $this->assertFalse($result['found'] ?? false, 'found flag must be false');
        $this->assertStringNotContainsString('BETA001', $result['_exact_summary']);
    }

    public function test_list_classes_only_returns_current_school_classes(): void
    {
        $result = $this->asSchool($this->schoolA, fn() =>
            $this->invoke('listClasses')
        );

        $this->assertStringContainsString('Alpha Form 1', $result['_exact_summary']);
        $this->assertStringNotContainsString('Beta Form 1', $result['_exact_summary']);
    }

    public function test_list_exams_only_returns_current_school_exams(): void
    {
        $result = $this->asSchool($this->schoolA, fn() =>
            $this->invoke('listExams')
        );

        $this->assertStringContainsString('Alpha Midterm', $result['_exact_summary']);
        $this->assertStringNotContainsString('Beta Midterm', $result['_exact_summary']);
    }

    public function test_list_subjects_only_returns_current_school_subjects(): void
    {
        $result = $this->asSchool($this->schoolA, fn() =>
            $this->invoke('listSubjects')
        );

        $this->assertStringContainsString('Alpha Physics', $result['_exact_summary']);
        $this->assertStringNotContainsString('Beta Chemistry', $result['_exact_summary']);
    }

    public function test_list_students_in_class_does_not_cross_school(): void
    {
        $result = $this->asSchool($this->schoolA, fn() =>
            $this->invoke('listStudentsInClass', 'Alpha Form 1')
        );

        $this->assertStringContainsString('AlphaFirst', $result['_exact_summary']);
        $this->assertStringNotContainsString('BetaFirst', $result['_exact_summary']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tests — raw DB::table() tools (the main leak risk)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_get_top_students_only_returns_current_school(): void
    {
        $result = $this->asSchool($this->schoolA, fn() =>
            $this->invoke('getTopStudents', null, null, 10)
        );

        $summary = $result['_exact_summary'];
        $this->assertStringContainsString('AlphaFirst', $summary,
            'Top students must include school A student');
        $this->assertStringNotContainsString('BetaFirst', $summary,
            'Top students must NOT include school B student');
    }

    public function test_get_class_performance_does_not_leak_other_school_marks(): void
    {
        $result = $this->asSchool($this->schoolA, fn() =>
            $this->invoke('getClassPerformance', 'Alpha Form 1', null)
        );

        $summary = $result['_exact_summary'];
        // School A student mark is 88 — should appear
        $this->assertStringContainsString('Alpha Physics', $summary,
            'Class performance must include school A subject');
        // School B mark is 77 — if it leaked, average would be (88+77)/2 = 82.5, not 88
        $this->assertStringContainsString('88', $summary,
            'Average must equal school A mark (88), not a blended value');
    }

    public function test_get_teacher_subjects_does_not_leak_other_school(): void
    {
        $result = $this->asSchool($this->schoolA, fn() =>
            $this->invoke('getTeacherSubjects')
        );

        $summary = $result['_exact_summary'];
        $this->assertStringContainsString('AlphaStaff', $summary,
            'Teacher list must include school A teacher');
        $this->assertStringNotContainsString('BetaStaff', $summary,
            'Teacher list must NOT include school B teacher');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tests — symmetric: school B sees its own data, not A's
    // ─────────────────────────────────────────────────────────────────────────

    public function test_school_b_sees_its_own_students_not_school_a(): void
    {
        $result = $this->asSchool($this->schoolB, fn() =>
            $this->invoke('searchStudent', 'BetaFirst')
        );

        $this->assertStringContainsString('BetaFirst', $result['_exact_summary']);
        $this->assertStringNotContainsString('AlphaFirst', $result['_exact_summary']);
    }

    public function test_school_b_top_students_excludes_school_a(): void
    {
        $result = $this->asSchool($this->schoolB, fn() =>
            $this->invoke('getTopStudents', null, null, 10)
        );

        $summary = $result['_exact_summary'];
        $this->assertStringContainsString('BetaFirst', $summary);
        $this->assertStringNotContainsString('AlphaFirst', $summary);
    }

    public function test_school_b_teacher_subjects_excludes_school_a(): void
    {
        $result = $this->asSchool($this->schoolB, fn() =>
            $this->invoke('getTeacherSubjects')
        );

        $this->assertStringContainsString('BetaStaff', $result['_exact_summary']);
        $this->assertStringNotContainsString('AlphaStaff', $result['_exact_summary']);
    }
}
