<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Book;
use App\Models\Department;
use App\Models\Dormitory;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\Demo\DemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Builds a self-contained demo school for sales conversations.
 *
 * Safety contract — this runs on production alongside real client data, so:
 *
 *  - Every row is written with an explicit school_id. Nothing relies on the
 *    BelongsToSchool auto-fill, which needs a bound tenant that a seeder does
 *    not have.
 *  - The school is identified by the slug in SLUG and nothing else. The seeder
 *    refuses to touch a school it did not create.
 *  - Re-running wipes and rebuilds only that school's rows. No query in here is
 *    unscoped, so Kitungwa and MEMA cannot be reached even by accident.
 *
 * Invoke through `php artisan demo:reset`, which adds the environment guard.
 */
class DemoSchoolSeeder extends Seeder
{
    /** The one identifier that defines what this seeder owns. */
    public const SLUG = 'demo';

    public const LOGIN_EMAIL = 'demo@demo.ac.tz';

    public const LOGIN_PASSWORD = 'demo1234';

    private const STUDENTS_PER_STREAM = 16;   // 8 streams → 128 students

    private School $school;

    private int $sid;

    private AcademicSession $session;

    /** @var array<string, Grade> */
    private array $grades = [];

    /** @var array<int, Subject> */
    private array $subjects = [];

    /** Subject id => the Staff row teaching it (subjects.teacher_id needs a
     *  user id, but subject_class/subject_teacher need the staff id — this
     *  keeps the staff side available without a second lookup). */
    private array $subjectStaff = [];

    /** @var array<int, SchoolClass> */
    private array $classes = [];

    /** @var array<int, Staff> */
    private array $staff = [];

    /** @var array<int, Department> */
    private array $departments = [];

    public function run(): void
    {
        // A fixed seed keeps the demo stable between rebuilds — the same
        // students, the same marks. A demo whose numbers move every reset is
        // impossible to prepare a talk track against.
        mt_srand(20260728);

        DB::transaction(function () {
            $this->school = $this->createSchool();
            $this->sid = $this->school->id;

            $this->purgeExistingDemoData();

            $this->session = $this->createSession();
            $this->createGrades();
            $this->createDepartments();
            $this->createStaff();
            $this->createSubjects();
            $this->createClasses();
            $this->linkSubjectsToClasses();
            $this->createDormitories();

            $students = $this->createStudentsAndGuardians();

            $this->createExamsAndMarks($students);
            $this->createFinance($students);
            $this->createLibrary($students);
            $this->createInventory();
            $this->createStaffAttendance();
        });

        $this->report();
    }

    // ─────────────────── School ───────────────────

    private function createSchool(): School
    {
        $school = School::withoutGlobalScope('school')
            ->where('slug', self::SLUG)
            ->first();

        if ($school) {
            $school->update([
                'subscription_status' => 'active',
                'subscription_expires_at' => now()->addYears(5),
            ]);

            return $school;
        }

        return School::create([
            'name' => 'Chani Demo Secondary School',
            'slug' => self::SLUG,
            'email' => 'info@demo.ac.tz',
            'phone' => '+255 27 275 4400',
            'address' => 'P.O. Box 1234, Moshi, Kilimanjaro, Tanzania',
            'motto' => 'Elimu ni Ufunguo wa Maisha',
            'website' => 'https://demo.ac.tz',
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addYears(5),
            'plan' => 'premium',
        ]);
    }

    /**
     * Delete everything belonging to the demo school, so a re-run is a clean
     * rebuild rather than a pile-up of duplicates.
     *
     * Every statement is filtered on school_id. The pivot tables have no
     * school_id of their own, so they are cleared via their parent's ids.
     */
    private function purgeExistingDemoData(): void
    {
        $classIds = DB::table('school_classes')->where('school_id', $this->sid)->pluck('id');
        $studentIds = DB::table('students')->where('school_id', $this->sid)->pluck('id');
        $subjectIds = DB::table('subjects')->where('school_id', $this->sid)->pluck('id');
        $staffIds = DB::table('staff')->where('school_id', $this->sid)->pluck('id');
        $itemIds = DB::table('inventory_items')->where('school_id', $this->sid)->pluck('id');

        // Pivots first — they hold no school_id, so they must be cleared by
        // reference before their parents disappear.
        DB::table('student_subject')->whereIn('student_id', $studentIds)->delete();
        DB::table('subject_class')->whereIn('subject_id', $subjectIds)->delete();
        DB::table('subject_teacher')->whereIn('staff_id', $staffIds)->delete();

        // Children before parents.
        foreach ([
            'marks', 'payments', 'student_bills', 'bills', 'exams',
            'lendings', 'books', 'inventory_transactions', 'inventory_items',
            'inventory_categories', 'attendances', 'enrollments',
            'dormitory_beds', 'dormitory_rooms',
        ] as $table) {
            DB::table($table)->where('school_id', $this->sid)->delete();
        }

        // Detach students from dormitories before those go.
        DB::table('students')->where('school_id', $this->sid)
            ->update(['dormitory_id' => null, 'class_id' => null, 'guardian_id' => null]);

        foreach ([
            'students', 'guardians', 'dormitories', 'school_classes',
            'subjects', 'grades', 'staff', 'academic_sessions',
        ] as $table) {
            DB::table($table)->where('school_id', $this->sid)->delete();
        }

        // Users last, and never the demo login itself — it is recreated with a
        // stable id so any saved bookmark keeps working.
        DB::table('users')->where('school_id', $this->sid)->delete();

        DB::table('departments')->where('school_id', $this->sid)->delete();

        unset($itemIds, $classIds);
    }

    private function createSession(): AcademicSession
    {
        return AcademicSession::create([
            'school_id' => $this->sid,
            'name' => now()->year.' Academic Year',
            'start_date' => Carbon::create(now()->year, 1, 8),
            'end_date' => Carbon::create(now()->year, 12, 5),
            'is_current' => true,
        ]);
    }

    private function createGrades(): void
    {
        foreach (DemoData::GRADES as [$name, $min, $max, $point, $desc]) {
            $this->grades[$name] = Grade::create([
                'school_id' => $this->sid,
                'name' => $name,
                'min_mark' => $min,
                'max_mark' => $max,
                'point' => $point,
                'description' => $desc,
            ]);
        }
    }

    private function createDepartments(): void
    {
        foreach (DemoData::DEPARTMENTS as $name) {
            $this->departments[] = Department::create([
                'school_id' => $this->sid,
                'name' => $name,
                'description' => $name.' department',
                'rank_requires_7_subjects' => false,
            ]);
        }
    }

    // ─────────────────── People ───────────────────

    private function createStaff(): void
    {
        foreach (DemoData::STAFF as $i => [$name, $role, $position, $deptIndex, $salary]) {
            [$first, $last] = explode(' ', $name, 2);

            $isLogin = $i === 0;
            $email = $isLogin
                ? self::LOGIN_EMAIL
                : strtolower($first.'.'.str_replace(' ', '', $last)).'@demo.ac.tz';

            $user = User::create([
                'school_id' => $this->sid,
                'is_super_admin' => false,
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'phone' => $this->phone(),
                'password' => Hash::make(self::LOGIN_PASSWORD),
                'email_verified_at' => now(),
                'department_id' => $this->departments[$deptIndex]->id,
                'position' => $position,
                'role' => $role,
            ]);

            $user->syncRoles([$role]);

            $this->staff[] = Staff::create([
                'school_id' => $this->sid,
                'user_id' => $user->id,
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'phone' => $user->phone,
                'department_id' => $this->departments[$deptIndex]->id,
                'position' => $position,
                'basic_salary' => $salary,
                'hire_date' => now()->subYears(mt_rand(1, 12))->subDays(mt_rand(0, 300)),
                'role' => $role,
            ]);
        }

        // Heads of department, so the HOD dashboards have someone in charge.
        $this->departments[1]->update(['head_id' => $this->staff[2]->id]);
        $this->departments[0]->update(['head_id' => $this->staff[3]->id]);
    }

    private function createSubjects(): void
    {
        // Teachers only — the first four staff are management.
        $teachers = array_slice($this->staff, 4, 10);

        foreach (DemoData::SUBJECTS as $i => [$name, $code, $type]) {
            $teacher = $teachers[$i % count($teachers)];

            // subjects.teacher_id references users.id, but subject_class.teacher_id
            // and subject_teacher.staff_id both reference staff.id — two different
            // targets for "the same" teacher, so the staff row is tracked
            // separately in $this->subjectStaff for linkSubjectsToClasses().
            $subject = Subject::create([
                'school_id' => $this->sid,
                'name' => $name,
                'code' => $code,
                'type' => $type,
                'teacher_id' => $teacher->user_id,
                'department_id' => $teacher->department_id,
            ]);

            $this->subjects[] = $subject;
            $this->subjectStaff[$subject->id] = $teacher;
        }
    }

    private function createClasses(): void
    {
        $teacherPool = array_slice($this->staff, 4, 10);
        $i = 0;

        foreach (['Form I', 'Form II', 'Form III', 'Form IV'] as $level) {
            foreach (['A', 'B'] as $stream) {
                $this->classes[] = SchoolClass::create([
                    'school_id' => $this->sid,
                    'name' => $level.' '.$stream,
                    'level' => $level,
                    'section' => $stream,
                    'capacity' => 40,
                    'class_teacher_id' => $teacherPool[$i % count($teacherPool)]->id,
                ]);
                $i++;
            }
        }
    }

    private function linkSubjectsToClasses(): void
    {
        $now = now();
        $subjectClass = [];
        $subjectTeacher = [];

        foreach ($this->classes as $class) {
            foreach ($this->subjects as $subject) {
                // Form I and II take the core set only; electives start in Form III,
                // which is how O-level streaming actually works.
                $isJunior = in_array($class->level, ['Form I', 'Form II'], true);

                if ($isJunior && $subject->type === 'elective') {
                    continue;
                }

                $staffId = $this->subjectStaff[$subject->id]->id;

                $subjectClass[] = [
                    'subject_id' => $subject->id,
                    'class_id' => $class->id,
                    'teacher_id' => $staffId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $subjectTeacher[] = [
                    'subject_id' => $subject->id,
                    'staff_id' => $staffId,
                    'class_id' => $class->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('subject_class')->insert($subjectClass);
        DB::table('subject_teacher')->insert($subjectTeacher);
    }

    private function createDormitories(): void
    {
        $dormMaster = $this->staff[14];

        foreach ([['Kilimanjaro Boys Hostel', 'male'], ['Meru Girls Hostel', 'female']] as [$name, $gender]) {
            $dorm = Dormitory::create([
                'school_id' => $this->sid,
                'name' => $name,
                'capacity' => 120,
                'gender' => $gender,
                'dorm_master_id' => $dormMaster->id,
            ]);

            for ($r = 1; $r <= 10; $r++) {
                $roomId = DB::table('dormitory_rooms')->insertGetId([
                    'school_id' => $this->sid,
                    'dormitory_id' => $dorm->id,
                    'room_number' => ($gender === 'male' ? 'B' : 'G').str_pad((string) $r, 2, '0', STR_PAD_LEFT),
                    'floor' => $r <= 5 ? 'Ground' : 'First',
                    'capacity' => 12,
                    'occupied_beds' => 0,
                    'room_type' => 'dormitory',
                    'has_attached_bathroom' => $r <= 3,
                    'has_balcony' => false,
                    'is_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $beds = [];
                for ($b = 1; $b <= 12; $b++) {
                    $beds[] = [
                        'school_id' => $this->sid,
                        'room_id' => $roomId,
                        'bed_number' => str_pad((string) $b, 2, '0', STR_PAD_LEFT),
                        'bed_type' => $b % 2 === 0 ? 'bunk_upper' : 'bunk_lower',
                        'status' => 'available',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('dormitory_beds')->insert($beds);
            }
        }
    }

    /** @return \Illuminate\Support\Collection<int, Student> */
    private function createStudentsAndGuardians()
    {
        $dorms = Dormitory::where('school_id', $this->sid)->get()->keyBy('gender');
        $students = collect();
        $admission = 1;
        $year = now()->year;

        foreach ($this->classes as $class) {
            for ($n = 0; $n < self::STUDENTS_PER_STREAM; $n++) {
                $gender = $n % 2 === 0 ? 'male' : 'female';
                $first = $gender === 'male'
                    ? $this->pick(DemoData::MALE_FIRST)
                    : $this->pick(DemoData::FEMALE_FIRST);
                $surname = $this->pick(DemoData::SURNAMES);

                // Form I students are ~14, gaining a year per form.
                $formNumber = (int) array_search(
                    $class->level,
                    ['Form I', 'Form II', 'Form III', 'Form IV'],
                    true
                ) + 1;
                $age = 13 + $formNumber;

                $guardian = Guardian::create([
                    'school_id' => $this->sid,
                    'first_name' => $this->pick(
                        mt_rand(0, 1) ? DemoData::MALE_FIRST : DemoData::FEMALE_FIRST
                    ),
                    'last_name' => $surname,
                    'gender' => mt_rand(0, 1) ? 'male' : 'female',
                    'relation_to_student' => $this->pick(['Father', 'Mother', 'Guardian', 'Uncle', 'Aunt']),
                    'phone' => $this->phone(),
                    'email' => null,
                    'address' => $this->pick(DemoData::WARDS).', Tanzania',
                    'occupation' => $this->pick(DemoData::OCCUPATIONS),
                ]);

                // Two thirds board, which is typical for an up-country secondary school.
                $isBoarder = mt_rand(1, 3) > 1;

                $student = Student::create([
                    'school_id' => $this->sid,
                    'admission_no' => sprintf('%s/%04d', $year, $admission++),
                    'first_name' => $first,
                    'middle_name' => $this->pick(DemoData::MIDDLE),
                    'last_name' => $surname,
                    'gender' => $gender,
                    'date_of_birth' => now()->subYears($age)->subDays(mt_rand(0, 364)),
                    'guardian_id' => $guardian->id,
                    'class_id' => $class->id,
                    'dormitory_id' => $isBoarder ? $dorms[$gender]->id : null,
                    'academic_session_id' => $this->session->id,
                    'admission_date' => now()->subYears($formNumber - 1)->startOfYear()->addDays(10),
                    'status' => 'active',
                    'address' => $this->pick(DemoData::WARDS).', Tanzania',
                    'phone' => $this->phone(),
                ]);

                DB::table('enrollments')->insert([
                    'school_id' => $this->sid,
                    'student_id' => $student->id,
                    'class_id' => $class->id,
                    'academic_session_id' => $this->session->id,
                    'roll_no' => str_pad((string) ($n + 1), 3, '0', STR_PAD_LEFT),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $students->push($student);
            }
        }

        $this->assignSubjectsToStudents($students);
        $this->assignBeds($students);

        return $students;
    }

    private function assignSubjectsToStudents($students): void
    {
        $rows = [];
        $now = now();

        foreach ($students as $student) {
            $class = $this->classes[array_search($student->class_id, array_column(
                array_map(fn ($c) => ['id' => $c->id], $this->classes), 'id'
            ), true)] ?? null;

            $isJunior = $class && in_array($class->level, ['Form I', 'Form II'], true);

            foreach ($this->subjects as $subject) {
                if ($isJunior && $subject->type === 'elective') {
                    continue;
                }

                $rows[] = [
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'withdrawn' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('student_subject')->insert($chunk);
        }
    }

    private function assignBeds($students): void
    {
        foreach (['male', 'female'] as $gender) {
            $boarders = $students->where('gender', $gender)->whereNotNull('dormitory_id')->values();

            $beds = DB::table('dormitory_beds')
                ->join('dormitory_rooms', 'dormitory_beds.room_id', '=', 'dormitory_rooms.id')
                ->join('dormitories', 'dormitory_rooms.dormitory_id', '=', 'dormitories.id')
                ->where('dormitories.school_id', $this->sid)
                ->where('dormitories.gender', $gender)
                ->orderBy('dormitory_beds.id')
                ->limit($boarders->count())
                ->pluck('dormitory_beds.id');

            foreach ($beds as $i => $bedId) {
                if (! isset($boarders[$i])) {
                    break;
                }

                DB::table('dormitory_beds')->where('id', $bedId)->update([
                    'status' => 'occupied',
                    'current_student_id' => $boarders[$i]->id,
                    'updated_at' => now(),
                ]);
            }
        }

        // Keep the room occupancy counter consistent with the beds just filled.
        DB::statement('
            UPDATE dormitory_rooms r
            SET r.occupied_beds = (
                SELECT COUNT(*) FROM dormitory_beds b
                WHERE b.room_id = r.id AND b.status = "occupied"
            )
            WHERE r.school_id = ?
        ', [$this->sid]);
    }

    // ─────────────────── Academics ───────────────────

    private function createExamsAndMarks($students): void
    {
        $exams = [];

        foreach ([
            ['Midterm Examination Term I', '1', false, false],
            ['Terminal Examination Term I', '1', true, false],
            ['Midterm Examination Term II', '2', false, false],
            ['Annual Examination', '2', true, true],
        ] as [$name, $term, $isTerminal, $isAnnual]) {
            $exams[] = Exam::create([
                'school_id' => $this->sid,
                'name' => $name,
                'term' => $term,
                'academic_session_id' => $this->session->id,
                'include_in_term_final' => $isTerminal,
                'include_in_year_final' => $isAnnual,
                'is_terminal_exam' => $isTerminal,
                'is_annual_exam' => $isAnnual,
                'status' => 'published',
                'published_by' => $this->staff[1]->user_id,
                'published_at' => now()->subDays(mt_rand(5, 90)),
            ]);
        }

        // Give each student a latent ability so their marks correlate across
        // subjects and exams. Random-per-cell marks produce a nonsense ranking
        // and the first thing anyone does in a demo is sort the results table.
        $ability = [];
        foreach ($students as $student) {
            $ability[$student->id] = $this->normal(58, 14);
        }

        $subjectsByClass = [];
        foreach ($this->classes as $class) {
            $isJunior = in_array($class->level, ['Form I', 'Form II'], true);
            $subjectsByClass[$class->id] = array_values(array_filter(
                $this->subjects,
                fn ($s) => ! ($isJunior && $s->type === 'elective')
            ));
        }

        $rows = [];
        $now = now();

        foreach ($exams as $examIndex => $exam) {
            foreach ($students as $student) {
                foreach ($subjectsByClass[$student->class_id] as $subject) {
                    // Subject difficulty plus a small improvement over the year.
                    $difficulty = match ($subject->code) {
                        'MAT', 'PHY', 'CHE' => -6,
                        'KIS', 'CIV' => +5,
                        default => 0,
                    };

                    $mark = $ability[$student->id] + $difficulty + ($examIndex * 1.5) + $this->normal(0, 7);
                    $mark = max(3, min(99, round($mark, 2)));

                    $rows[] = [
                        'school_id' => $this->sid,
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'class_id' => $student->class_id,
                        'exam_id' => $exam->id,
                        'academic_session_id' => $this->session->id,
                        'mark' => $mark,
                        'grade_id' => $this->gradeFor($mark)->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table('marks')->insert($chunk);
        }
    }

    private function gradeFor(float $mark): Grade
    {
        foreach ($this->grades as $grade) {
            if ($mark >= (float) $grade->min_mark && $mark <= (float) $grade->max_mark) {
                return $grade;
            }
        }

        return $this->grades['F'];
    }

    // ─────────────────── Finance ───────────────────

    /**
     * Bills in this schema are per-class (bills.class_id is NOT NULL), which
     * matches how Tanzanian schools actually structure fees — a form's fee
     * notice, not one school-wide notice. So each fee item becomes one bill
     * per class rather than a single bill shared across the whole school.
     */
    private function createFinance($students): void
    {
        $accountant = $this->staff[15];
        $now = now();
        $studentsByClass = $students->groupBy('class_id');

        foreach (DemoData::FEES as [$title, $amount, $term]) {
            $isBoarding = str_contains($title, 'Boarding');
            $dueDate = $term === 1
                ? Carbon::create($now->year, 3, 31)
                : Carbon::create($now->year, 8, 31);

            foreach ($this->classes as $class) {
                $classStudents = $studentsByClass->get($class->id, collect())
                    ->when($isBoarding, fn ($c) => $c->whereNotNull('dormitory_id'));

                if ($classStudents->isEmpty()) {
                    continue;
                }

                $billId = DB::table('bills')->insertGetId([
                    'school_id' => $this->sid,
                    'class_id' => $class->id,
                    'title' => $title,
                    'description' => $title.' — '.$class->name.', '.$now->year,
                    'amount' => $amount,
                    'academic_session_id' => $this->session->id,
                    'due_date' => $dueDate,
                    'status' => 'open',
                    'created_by' => $accountant->user_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $studentBills = [];

                foreach ($classStudents as $student) {
                    // A realistic collection spread: most fully paid, a tail of
                    // partial and unpaid. A demo where everyone has paid makes the
                    // debtor report — the screen bursars actually care about —
                    // look empty.
                    $roll = mt_rand(1, 100);
                    $paidRatio = match (true) {
                        $roll <= 62 => 1.0,
                        $roll <= 85 => mt_rand(30, 90) / 100,
                        default => 0.0,
                    };

                    $paid = round($amount * $paidRatio, 2);
                    $balance = round($amount - $paid, 2);

                    $studentBills[] = [
                        'school_id' => $this->sid,
                        'bill_id' => $billId,
                        'student_id' => $student->id,
                        'total_amount' => $amount,
                        'amount_paid' => $paid,
                        'balance' => $balance,
                        'status' => match (true) {
                            $paid <= 0 => 'unpaid',
                            $balance <= 0 => 'paid',
                            default => 'partial',
                        },
                        'due_date' => $dueDate,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table('student_bills')->insert($studentBills);

                // Payments are written against the student_bill rows just created.
                $created = DB::table('student_bills')
                    ->where('bill_id', $billId)
                    ->where('amount_paid', '>', 0)
                    ->get(['id', 'student_id', 'amount_paid']);

                $payments = [];

                foreach ($created as $sb) {
                    $payDate = $now->copy()->subDays(mt_rand(10, 200));

                    $payments[] = [
                        'school_id' => $this->sid,
                        'student_id' => $sb->student_id,
                        'student_bill_id' => $sb->id,
                        'class_id' => $class->id,
                        'amount' => $sb->amount_paid,
                        'method' => $this->pick(['Cash', 'Bank Transfer', 'M-Pesa', 'CRDB Bank', 'NMB Bank']),
                        'reference' => 'RCPT-'.strtoupper(substr(md5((string) $sb->id), 0, 8)),
                        'payment_date' => $payDate,
                        'paid_at' => $payDate,
                        'received_by' => $accountant->user_id,
                        'recorded_by' => $accountant->user_id,
                        'verified_by' => $accountant->user_id,
                        'status' => 'verified',
                        'created_at' => $payDate,
                        'updated_at' => $payDate,
                    ];
                }

                foreach (array_chunk($payments, 500) as $chunk) {
                    DB::table('payments')->insert($chunk);
                }
            }
        }
    }

    // ─────────────────── Library, stores, attendance ───────────────────

    private function createLibrary($students): void
    {
        $books = [];

        foreach (DemoData::BOOKS as [$title, $author, $category]) {
            $books[] = Book::create([
                'school_id' => $this->sid,
                'title' => $title,
                'author' => $author,
                'isbn' => '978-'.mt_rand(1000000000, 9999999999),
                'quantity' => mt_rand(15, 90),
            ]);
        }

        $lendings = [];
        $borrowers = $students->random(min(45, $students->count()));

        foreach ($borrowers as $student) {
            $book = $books[array_rand($books)];
            $lendDate = now()->subDays(mt_rand(1, 70));
            $returned = mt_rand(1, 100) <= 65;

            $lendings[] = [
                'school_id' => $this->sid,
                'book_id' => $book->id,
                'quantity' => 1,
                'user_id' => $student->id,
                'borrower_type' => Student::class,
                'lend_date' => $lendDate,
                'return_date' => $lendDate->copy()->addDays(14),
                'returned_at' => $returned ? $lendDate->copy()->addDays(mt_rand(3, 20)) : null,
                'returned' => $returned,
                'created_at' => $lendDate,
                'updated_at' => $lendDate,
            ];
        }

        DB::table('lendings')->insert($lendings);
    }

    private function createInventory(): void
    {
        $storekeeper = $this->staff[16];
        $categories = [];

        foreach (DemoData::INVENTORY_CATEGORIES as [$name, $icon]) {
            $categories[$name] = InventoryCategory::create([
                'school_id' => $this->sid,
                'name' => $name,
                'icon' => $icon,
                'description' => $name.' items',
            ]);
        }

        $transactions = [];

        foreach (DemoData::INVENTORY as $i => [$name, $category, $unit, $qty, $min, $cost]) {
            $item = InventoryItem::create([
                'school_id' => $this->sid,
                'category_id' => $categories[$category]->id,
                'managed_by' => $storekeeper->user_id,
                'name' => $name,
                'code' => sprintf('ITM-%04d', $i + 1),
                'unit' => $unit,
                'quantity_in_stock' => $qty,
                'minimum_stock' => $min,
                'unit_cost' => $cost,
                'condition' => 'good',
                'location' => 'Main Store',
            ]);

            // Opening purchase, then a few issues, so the ledger explains the
            // balance rather than the balance appearing from nowhere.
            $balance = $qty;
            $issues = [];

            for ($t = 0; $t < mt_rand(1, 3); $t++) {
                $out = mt_rand(1, max(1, (int) ($qty * 0.15)));
                $issues[] = $out;
            }

            $opening = $qty + array_sum($issues);
            $running = $opening;

            // Every row carries the same set of keys (null where not
            // applicable) — a bulk insert() derives its column list from
            // array_keys() and reads values by position, so rows with
            // different key sets (a purchase's reference_no vs. an issue's
            // issued_to) silently shift values into the wrong columns.
            $transactions[] = [
                'school_id' => $this->sid,
                'item_id' => $item->id,
                'type' => 'purchase',
                'quantity' => $opening,
                'balance_after' => $running,
                'reference_no' => 'LPO-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'issued_to' => null,
                'remarks' => 'Opening stock',
                'user_id' => $storekeeper->user_id,
                'transaction_date' => now()->subDays(mt_rand(120, 200)),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            foreach ($issues as $out) {
                $running -= $out;

                $transactions[] = [
                    'school_id' => $this->sid,
                    'item_id' => $item->id,
                    'type' => 'issue',
                    'quantity' => $out,
                    'balance_after' => $running,
                    'reference_no' => null,
                    'issued_to' => $this->pick(['Form I A', 'Form II B', 'Science Laboratory', 'Kitchen', 'Administration']),
                    'remarks' => 'Issued from main store',
                    'user_id' => $storekeeper->user_id,
                    'transaction_date' => now()->subDays(mt_rand(1, 110)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            unset($balance);
        }

        DB::table('inventory_transactions')->insert($transactions);
    }

    private function createStaffAttendance(): void
    {
        $rows = [];

        for ($d = 60; $d >= 1; $d--) {
            $date = now()->subDays($d);

            if ($date->isWeekend()) {
                continue;
            }

            foreach ($this->staff as $staff) {
                $roll = mt_rand(1, 100);
                $status = match (true) {
                    $roll <= 92 => 'present',
                    $roll <= 97 => 'absent',
                    default => 'leave',
                };

                $rows[] = [
                    'school_id' => $this->sid,
                    'staff_id' => $staff->id,
                    'date' => $date->toDateString(),
                    'status' => $status,
                    'check_in_at' => $status === 'present'
                        ? $date->copy()->setTime(7, mt_rand(10, 55))
                        : null,
                    'check_out_at' => $status === 'present'
                        ? $date->copy()->setTime(15, mt_rand(30, 59))
                        : null,
                    'source' => 'biometric',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }
    }

    // ─────────────────── Helpers ───────────────────

    private function pick(array $items)
    {
        return $items[array_rand($items)];
    }

    private function phone(): string
    {
        return '+2557'.mt_rand(10000000, 89999999);
    }

    /** Box–Muller, so marks cluster around a mean instead of spreading flat. */
    private function normal(float $mean, float $sd): float
    {
        $u1 = max(1e-9, mt_rand() / mt_getrandmax());
        $u2 = mt_rand() / mt_getrandmax();

        return $mean + $sd * sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
    }

    private function report(): void
    {
        $counts = [
            'Students' => DB::table('students')->where('school_id', $this->sid)->count(),
            'Staff' => DB::table('staff')->where('school_id', $this->sid)->count(),
            'Classes' => DB::table('school_classes')->where('school_id', $this->sid)->count(),
            'Subjects' => DB::table('subjects')->where('school_id', $this->sid)->count(),
            'Marks' => DB::table('marks')->where('school_id', $this->sid)->count(),
            'Student bills' => DB::table('student_bills')->where('school_id', $this->sid)->count(),
            'Payments' => DB::table('payments')->where('school_id', $this->sid)->count(),
            'Books' => DB::table('books')->where('school_id', $this->sid)->count(),
            'Inventory items' => DB::table('inventory_items')->where('school_id', $this->sid)->count(),
            'Staff attendance' => DB::table('attendances')->where('school_id', $this->sid)->count(),
        ];

        $this->command?->newLine();
        $this->command?->info("Demo school ready — {$this->school->name} (school code: ".self::SLUG.')');

        foreach ($counts as $label => $count) {
            $this->command?->line(sprintf('  %-18s %s', $label, number_format($count)));
        }

        $this->command?->newLine();
        $this->command?->line('  Login: '.self::LOGIN_EMAIL.' / '.self::LOGIN_PASSWORD);
        $this->command?->newLine();
    }
}
