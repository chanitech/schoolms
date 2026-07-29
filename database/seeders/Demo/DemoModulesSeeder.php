<?php

namespace Database\Seeders\Demo;

use App\Models\AcademicSession;
use App\Models\Book;
use App\Models\Category;
use App\Models\ClassroomGuidance;
use App\Models\CounselingIntakeForm;
use App\Models\DailyReport;
use App\Models\Department;
use App\Models\Division;
use App\Models\Document;
use App\Models\Dormitory;
use App\Models\DormitoryBed;
use App\Models\Event;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\IndividualSessionReport;
use App\Models\InterestInventory;
use App\Models\InventoryItem;
use App\Models\JobCard;
use App\Models\JobDescription;
use App\Models\Leave;
use App\Models\LessonPlan;
use App\Models\LoanCategory;
use App\Models\Mark;
use App\Models\PocketTransaction;
use App\Models\ProcurementRequest;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolInfo;
use App\Models\Staff;
use App\Models\StockRequest;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fills in every remaining module the sidebar exposes so a prospect never
 * lands on an empty screen mid-walkthrough.
 *
 * Deliberately independent of DemoSchoolSeeder's in-memory state: it resolves
 * the demo school and re-queries what it needs, the same way any other
 * request would. That makes it re-runnable on its own and keeps it from
 * growing DemoSchoolSeeder into an unmanageable single file.
 *
 * Two things this intentionally does NOT seed:
 *  - group_counseling_session_reports: the table has no school_id column
 *    despite the model using BelongsToSchool, so every query against it
 *    throws in production today regardless of this seeder. A pre-existing
 *    bug, not something to route around here.
 *  - ai_conversations / ai_messages: seeding fake AI chat history reads as
 *    fabricated in a sales demo in a way empty data doesn't.
 */
class DemoModulesSeeder extends Seeder
{
    private School $school;

    private int $sid;

    public function run(): void
    {
        $this->school = School::where('slug', \Database\Seeders\DemoSchoolSeeder::SLUG)->firstOrFail();
        $this->sid = $this->school->id;

        app()->instance('currentSchool', $this->school);

        DB::transaction(function () {
            self::purgeForSchool($this->sid);

            $session = AcademicSession::where('is_current', true)->firstOrFail();
            $students = Student::all();
            $staff = Staff::all();
            $classes = SchoolClass::all();
            $subjects = Subject::all();
            $departments = Department::all();
            $dormitories = Dormitory::all();

            $this->seedSchoolInfo();
            $this->seedDivisionsAndResults($session);
            $this->seedBookCategories();
            $this->seedTimetable($session, $classes, $subjects, $staff);
            $this->seedHr($staff, $departments);
            $this->seedTreasurerOffice($staff, $departments);
            $this->seedProcurement($staff);
            $this->seedLearningProfileAndCounseling($students, $staff);
            $this->seedDocumentsAndSignatures($staff, $session, $classes);
            $this->seedPocketMoney($students, $staff);
            $this->seedDormitoryAllocations($dormitories, $session, $staff);
            $this->seedDailyReportsAndLessonPlans($staff, $session, $classes, $subjects);
            $this->seedAccountantAssignments($staff, $classes);
            $this->seedTaskLogs($staff);
        });

        $this->command?->info('Demo modules seeded — timetable, HR, treasury, procurement, counseling, documents, and more.');
    }

    /**
     * Wipe this seeder's own tables for the given school, so reruns don't
     * duplicate.
     *
     * Public and static so DemoSchoolSeeder can call it *before* deleting its
     * own core tables (classes, subjects, staff) — several tables here
     * (timetable_entries, lesson_plans, ...) hold foreign keys into those
     * core tables, so this must run first or the core-table delete fails on
     * a second run with rows already in place.
     */
    public static function purgeForSchool(int $schoolId): void
    {
        foreach ([
            'lesson_subtopics', 'lesson_topics', 'lesson_plans',
            'daily_report_activities', 'daily_reports',
            'accountant_class_assignments',
            'task_logs',
            'dormitory_bed_allocations',
            'pocket_transactions',
            'document_signatures', 'documents',
            'individual_session_reports', 'counseling_intake_forms',
            'classroom_guidances', 'interest_inventories',
            'aptitude_answers', 'aptitude_attempts', 'aptitude_questions',
            'stock_requests', 'procurement_requests',
            'loan_repayments', 'loans', 'loan_categories', 'bank_statements',
            'invoices', 'budget_items', 'budgets', 'expense_logs',
            'events', 'job_cards', 'job_descriptions', 'leaves', 'staff_salary_history',
            'timetable_reviews', 'timetable_session_logs', 'timetable_entries', 'timetables', 'timetable_periods',
            'student_results', 'divisions',
            'categories',
            'school_infos',
        ] as $table) {
            DB::table($table)->where('school_id', $schoolId)->delete();
        }
    }

    // ─────────────────── School profile ───────────────────

    private function seedSchoolInfo(): void
    {
        SchoolInfo::create([
            'school_id' => $this->sid,
            'name' => $this->school->name,
            'motto' => $this->school->motto,
            'email' => $this->school->email,
            'phone' => $this->school->phone,
            'address' => $this->school->address,
            'website' => $this->school->website,
            'lock_results_for_guardians' => false,
            'lock_results_only_overdue' => true,
        ]);
    }

    // ─────────────────── Divisions & results ───────────────────

    /**
     * Standard NECTA CSEE division bands. With 7 best-subject points each
     * ranging 1 (A) to 5 (F), the sum spans 7 (all A) to 35 (all F) — the
     * bands below partition that whole range, so every student lands in one.
     */
    private function seedDivisionsAndResults(AcademicSession $session): void
    {
        $bands = [
            ['I', 7, 17, 'Distinction'],
            ['II', 18, 21, 'Credit'],
            ['III', 22, 25, 'Credit'],
            ['IV', 26, 33, 'Pass'],
            ['0', 34, 35, 'Fail'],
        ];

        foreach ($bands as [$name, $min, $max, $desc]) {
            Division::create([
                'school_id' => $this->sid,
                'name' => $name,
                'min_points' => $min,
                'max_points' => $max,
                'description' => $desc,
            ]);
        }

        // Mirrors StudentResultController's own algorithm exactly: best 7
        // subjects by lowest grade point (core filled first, electives fill
        // remaining slots), summed for total_points, averaged for GPA, banded
        // through the divisions table just seeded above.
        $divisions = Division::all();
        $gradeByName = Grade::all()->keyBy('name');

        // Only the exams flagged terminal/annual get published results —
        // matching how a real school only formally computes results at
        // reporting checkpoints, not after every midterm.
        $reportableExams = Exam::where(fn ($q) => $q->where('is_terminal_exam', true)->orWhere('is_annual_exam', true))->get();

        $rows = [];
        $now = now();

        foreach ($reportableExams as $exam) {
            $marksByStudent = Mark::where('exam_id', $exam->id)
                ->with('subject')
                ->get()
                ->groupBy('student_id');

            foreach ($marksByStudent as $studentId => $marks) {
                $scored = $marks->map(fn ($m) => [
                    'type' => $m->subject->type,
                    'point' => (float) $gradeByName->firstWhere('id', $m->grade_id)?->point,
                ]);

                $core = $scored->where('type', 'core')->sortBy('point')->values();
                $electives = $scored->where('type', 'elective')->sortBy('point')->values();
                $coreSlots = min(7, $core->count());
                $best = $core->take($coreSlots)->merge($electives->take(7 - $coreSlots));

                $count = max(1, $best->count());
                $totalPoints = (int) $best->sum('point');
                $gpa = round($totalPoints / $count, 2);
                $division = $divisions->first(fn ($d) => $totalPoints >= $d->min_points && $totalPoints <= $d->max_points);

                $rows[] = [
                    'school_id' => $this->sid,
                    'student_id' => $studentId,
                    'exam_id' => $exam->id,
                    'gpa' => $gpa,
                    'total_points' => $totalPoints,
                    'division' => $division?->name ?? '0',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('student_results')->insert($chunk);
        }
    }

    // ─────────────────── Library categories ───────────────────

    private function seedBookCategories(): void
    {
        $categories = [];

        foreach (['Textbooks', 'Literature', 'Reference'] as $name) {
            $categories[$name] = Category::create(['school_id' => $this->sid, 'name' => $name]);
        }

        foreach (DemoData::BOOKS as [$title, , $categoryName]) {
            Book::where('school_id', $this->sid)->where('title', $title)
                ->update(['category_id' => $categories[$categoryName]->id]);
        }
    }

    // ─────────────────── Timetable ───────────────────

    private function seedTimetable(AcademicSession $session, $classes, $subjects, $staff): void
    {
        $periods = [
            ['Period 1', '07:40', '08:20', false],
            ['Period 2', '08:20', '09:00', false],
            ['Period 3', '09:00', '09:40', false],
            ['Break', '09:40', '10:00', true],
            ['Period 4', '10:00', '10:40', false],
            ['Period 5', '10:40', '11:20', false],
            ['Period 6', '11:20', '12:00', false],
            ['Lunch', '12:00', '13:00', true],
            ['Period 7', '13:00', '13:40', false],
            ['Period 8', '13:40', '14:20', false],
        ];

        $periodModels = [];
        foreach ($periods as $i => [$name, $start, $end, $isBreak]) {
            $periodModels[] = DB::table('timetable_periods')->insertGetId([
                'school_id' => $this->sid,
                'name' => $name,
                'start_time' => $start,
                'end_time' => $end,
                'is_break' => $isBreak,
                'is_active' => true,
                'order_no' => $i + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $teachingPeriods = array_values(array_filter($periods, fn ($p) => ! $p[3]));
        $teachingPeriodIds = [];
        foreach ($periodModels as $i => $id) {
            if (! $periods[$i][3]) {
                $teachingPeriodIds[] = $id;
            }
        }

        $principal = $staff->first();

        $timetable = Timetable::create([
            'school_id' => $this->sid,
            'title' => now()->year.' Academic Year Class Timetable',
            'type' => 'class',
            'academic_session_id' => $session->id,
            'status' => 'published',
            'class_ids' => $classes->pluck('id')->all(),
            'created_by' => $principal->user_id,
            'published_by' => $principal->user_id,
            'published_at' => now()->subDays(30),
        ]);

        // subject_class carries the teacher assigned to teach a subject in a
        // given class — reuse it so the timetable lines up with who actually
        // teaches what, rather than assigning teachers at random. Its
        // teacher_id is a staff.id, but timetable_entries.teacher_id
        // references users.id, so each row is translated through staff.
        // Scoped explicitly to our own classes — subject_class has no
        // school_id column, so an unfiltered query here would mix in other
        // schools' rows (harmless here only by coincidence of how the lookup
        // below is keyed; not something to rely on).
        $assignments = DB::table('subject_class')
            ->whereIn('class_id', $classes->pluck('id'))
            ->get()
            ->groupBy('class_id');
        $staffUserIdById = $staff->pluck('user_id', 'id');

        $rows = [];
        foreach ($classes as $class) {
            $classSubjects = $assignments->get($class->id, collect())->values();

            if ($classSubjects->isEmpty()) {
                continue;
            }

            foreach (range(1, 5) as $day) { // Mon–Fri
                foreach ($teachingPeriodIds as $slotIndex => $periodId) {
                    $subjectRow = $classSubjects[($day * 3 + $slotIndex) % $classSubjects->count()];

                    $rows[] = [
                        'school_id' => $this->sid,
                        'timetable_id' => $timetable->id,
                        'class_id' => $class->id,
                        'subject_id' => $subjectRow->subject_id,
                        'teacher_id' => $staffUserIdById->get($subjectRow->teacher_id),
                        'invigilator_ids' => null,
                        'day_of_week' => $day,
                        'period_id' => $periodId,
                        'exam_date' => null,
                        'start_time' => null,
                        'end_time' => null,
                        'room' => $class->name,
                        'notes' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('timetable_entries')->insert($chunk);
        }

        DB::table('timetable_reviews')->insert([
            'school_id' => $this->sid,
            'timetable_id' => $timetable->id,
            'reviewer_id' => $principal->user_id,
            'reviewer_role' => 'Principal',
            'action' => 'approved',
            'notes' => 'Approved for the academic year.',
            'reviewed_at' => now()->subDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ─────────────────── HR ───────────────────

    private function seedHr($staff, $departments): void
    {
        $principal = $staff->first();

        // Leaves — a mix so the approvals queue isn't empty either.
        $leaveTypes = ['sick', 'casual', 'annual', 'other'];
        foreach ($staff->slice(2, 8) as $i => $member) {
            $start = now()->subDays(mt_rand(5, 60));

            Leave::create([
                'school_id' => $this->sid,
                'staff_id' => $member->id,
                'requested_to' => $principal->id,
                'type' => $leaveTypes[$i % count($leaveTypes)],
                'reason' => 'Personal matters requiring time off.',
                'start_date' => $start,
                'end_date' => $start->copy()->addDays(mt_rand(1, 5)),
                'status' => $i % 4 === 0 ? 'pending' : ($i % 4 === 1 ? 'rejected' : 'approved'),
            ]);
        }

        // Job cards — day-to-day tasking between staff.
        $tasks = [
            'Prepare mid-term exam timetable', 'Update inventory stock count',
            'Organise inter-form sports day', 'Review Form IV mock results',
            'Compile termly attendance report', 'Coordinate parents\' meeting logistics',
            'Audit library book returns', 'Draft new staff induction pack',
        ];
        foreach ($tasks as $i => $title) {
            $assignee = $staff[($i + 1) % $staff->count()];

            JobCard::create([
                'school_id' => $this->sid,
                'title' => $title,
                'description' => $title.' for '.now()->year.' Academic Year.',
                'assigned_by' => $principal->id,
                'assigned_to' => $assignee->id,
                'status' => $i % 3 === 0 ? 'pending' : ($i % 3 === 1 ? 'in_progress' : 'completed'),
                'rating' => $i % 3 === 2 ? mt_rand(3, 5) : null,
                'due_date' => now()->addDays(mt_rand(-10, 20)),
            ]);
        }

        // Job descriptions per role.
        foreach ([
            'Principal' => 'Overall academic and administrative leadership of the school.',
            'Teacher' => 'Deliver the syllabus, assess learners, and maintain classroom records.',
            'Dorm Master' => 'Supervise boarding students\' welfare, discipline and safety.',
            'accountant' => 'Manage fee collection, payments and financial records.',
            'storekeeper' => 'Maintain inventory records and issue stock against approved requests.',
        ] as $role => $desc) {
            JobDescription::create([
                'school_id' => $this->sid,
                'role_name' => $role,
                'description' => $desc,
                'updated_by' => $principal->user_id,
            ]);
        }

        // School events — a mix of past and upcoming, for the calendar.
        $events = [
            ['Term I Opening', 'academic', -180, -180],
            ['Mid-Term Examinations', 'academic', -90, -85],
            ['Inter-House Sports Day', 'sport', -40, -40],
            ['Cultural Day', 'cultural', -20, -20],
            ['Term I Closing', 'academic', -10, -10],
            ['Term II Opening', 'academic', 5, 5],
            ['Annual Examinations', 'academic', 60, 65],
            ['Independence Day Holiday', 'holiday', 15, 15],
            ['Parents\' Day', 'other', 30, 30],
        ];
        foreach ($events as [$title, $type, $startOffset, $endOffset]) {
            Event::create([
                'school_id' => $this->sid,
                'title' => $title,
                'department_id' => $departments->random()->id,
                'type' => $type,
                'start_date' => now()->addDays($startOffset),
                'end_date' => now()->addDays($endOffset),
                'description' => $title.' — school-wide.',
                'created_by' => $principal->id,
            ]);
        }

        // Salary history — one raise per staff member, showing the trail.
        foreach ($staff as $member) {
            $old = (float) $member->basic_salary;
            $new = round($old * 1.08);

            \App\Models\StaffSalaryHistory::create([
                'school_id' => $this->sid,
                'staff_id' => $member->id,
                'old_salary' => round($old / 1.08),
                'new_salary' => $old,
                'effective_date' => now()->subMonths(6),
                'changed_by' => $principal->user_id,
                'reason' => 'Annual increment.',
            ]);
        }
    }

    // ─────────────────── Treasurer Office ───────────────────

    private function seedTreasurerOffice($staff, $departments): void
    {
        $accountant = $staff[15] ?? $staff->last();
        $treasurer = $staff->firstWhere('role', 'accountant') ?? $accountant;

        // Budgets with line items across a few departments.
        $budgetItems = [
            ['Laboratory chemicals restock', 850000],
            ['Sports equipment', 420000],
            ['Library book acquisition', 600000],
            ['Classroom furniture repair', 380000],
            ['Kitchen supplies', 950000],
        ];

        foreach (['January', 'February', 'March'] as $month) {
            $budget = \App\Models\Budget::create([
                'school_id' => $this->sid,
                'staff_id' => $accountant->user_id,
                'department_id' => $departments->random()->id,
                'month' => $month,
                'year' => now()->year,
                'status' => 'approved',
                'current_step' => 'completed',
                'total_amount' => array_sum(array_column($budgetItems, 1)),
                'note' => $month.' operating budget.',
            ]);

            foreach ($budgetItems as [$item, $price]) {
                $budgetItem = \App\Models\BudgetItem::create([
                    'school_id' => $this->sid,
                    'budget_id' => $budget->id,
                    'item' => $item,
                    'description' => $item,
                    'price' => $price,
                    'status' => 'approved',
                    'approved_by' => $accountant->user_id,
                ]);

                \App\Models\Invoice::create([
                    'school_id' => $this->sid,
                    'budget_item_id' => $budgetItem->id,
                    'budget_id' => $budget->id,
                    'amount' => $price,
                    'status' => 'paid',
                    'notes' => 'Paid against approved budget item.',
                    'approved_by_do_id' => $staff->first()->user_id,
                    'paid_by_finance_id' => $accountant->user_id,
                    'payment_date' => now()->subDays(mt_rand(5, 60)),
                ]);
            }
        }

        // Loan categories and a few staff loans in different states.
        $emergencyLoans = LoanCategory::create([
            'school_id' => $this->sid,
            'name' => 'Emergency Loan',
            'description' => 'Short-term loan for urgent personal needs.',
            'min_amount' => 50000,
            'max_amount' => 1000000,
            'max_installments' => 12,
            'interest_rate' => 5,
            'eligibility_criteria' => ['min_service_months' => 6],
            'restrictions' => ['max_active_loans' => 1],
            'created_by_treasurer_id' => $treasurer->user_id,
            'is_active' => true,
        ]);

        $developmentLoans = LoanCategory::create([
            'school_id' => $this->sid,
            'name' => 'Development Loan',
            'description' => 'Longer-term loan for major personal projects.',
            'min_amount' => 500000,
            'max_amount' => 5000000,
            'max_installments' => 36,
            'interest_rate' => 8,
            'eligibility_criteria' => ['min_service_months' => 24],
            'restrictions' => null,
            'created_by_treasurer_id' => $treasurer->user_id,
            'is_active' => true,
        ]);

        foreach ($staff->slice(2, 4) as $i => $member) {
            $category = $i % 2 === 0 ? $emergencyLoans : $developmentLoans;
            $applied = $i % 2 === 0 ? 500000 : 2000000;
            $applicationDate = now()->subMonths(4);

            $loan = \App\Models\Loan::create([
                'school_id' => $this->sid,
                'staff_id' => $member->id,
                'loan_category_id' => $category->id,
                'amount_applied' => $applied,
                'amount_approved' => $applied,
                'interest_rate_applied' => $category->interest_rate,
                'installments' => $i % 2 === 0 ? 6 : 18,
                'salary_at_application' => (float) $member->basic_salary,
                'application_date' => $applicationDate,
                'approval_date' => $applicationDate->copy()->addDays(3),
                'disbursement_date' => $applicationDate->copy()->addDays(5),
                'expected_end_date' => $applicationDate->copy()->addMonths($i % 2 === 0 ? 6 : 18),
                'approval_level' => 3,
                'chief_accountant_approved_by' => $accountant->user_id,
                'chief_accountant_approved_at' => $applicationDate->copy()->addDay(),
                'accountant_approved_by' => $accountant->user_id,
                'accountant_approved_at' => $applicationDate->copy()->addDays(2),
                'treasurer_approved_by' => $treasurer->user_id,
                'treasurer_approved_at' => $applicationDate->copy()->addDays(3),
                'status' => 'active',
                'treasurer_notes' => 'Approved — good repayment history.',
            ]);

            $installmentAmount = round($applied / ($i % 2 === 0 ? 6 : 18), 2);
            $count = $i % 2 === 0 ? 6 : 18;

            for ($n = 1; $n <= $count; $n++) {
                $dueDate = $applicationDate->copy()->addMonths($n);

                \App\Models\LoanRepayment::create([
                    'school_id' => $this->sid,
                    'loan_id' => $loan->id,
                    'installment_number' => $n,
                    'amount' => $installmentAmount,
                    'due_date' => $dueDate,
                    'paid_date' => $dueDate->isPast() ? $dueDate : null,
                    'status' => $dueDate->isPast() ? 'paid' : 'pending',
                    'payment_reference' => $dueDate->isPast() ? 'PAY-'.strtoupper(Str::random(6)) : null,
                ]);
            }
        }

        // Expense log — a running ledger of school spend.
        foreach ([
            ['Utilities', 320000], ['Fuel', 180000], ['Maintenance', 250000],
            ['Stationery', 95000], ['Transport', 140000], ['Medical supplies', 110000],
        ] as [$category, $amount]) {
            \App\Models\ExpenseLog::create([
                'school_id' => $this->sid,
                'recorded_by' => $accountant->user_id,
                'category' => $category,
                'amount' => $amount,
                'notes' => $category.' expense for '.now()->format('F Y').'.',
            ]);
        }

        $this->seedBankStatements($staff, $accountant);
    }

    /**
     * Bank statements are a real file upload in this app — a row with no
     * matching file on disk would 404 the moment someone clicks "view".
     * A minimal valid single-page PDF is written to the same 'public' disk
     * Documents uses, so the download link actually resolves.
     */
    private function seedBankStatements($staff, $accountant): void
    {
        foreach ($staff->slice(0, 3) as $member) {
            $month = now()->subMonth()->startOfMonth();
            $filename = 'bank_statements/'.$this->sid.'/'.Str::slug($member->first_name.'-'.$member->last_name).'-'.$month->format('Y-m').'.pdf';

            Storage::disk('public')->put($filename, $this->placeholderPdf(
                'Bank Statement',
                $member->first_name.' '.$member->last_name.' — '.$month->format('F Y')
            ));

            \App\Models\BankStatement::create([
                'school_id' => $this->sid,
                'staff_id' => $member->id,
                'file_path' => $filename,
                'original_name' => basename($filename),
                'mime_type' => 'application/pdf',
                'file_size' => Storage::disk('public')->size($filename),
                'statement_month' => $month,
                'uploaded_by' => $accountant->user_id,
            ]);
        }
    }

    // ─────────────────── Procurement ───────────────────

    private function seedProcurement($staff): void
    {
        $storekeeper = $staff->firstWhere('role', 'storekeeper') ?? $staff->last();
        $principal = $staff->first();
        $items = InventoryItem::where('school_id', $this->sid)->inRandomOrder()->take(6)->get();

        foreach ($items as $i => $item) {
            $qty = mt_rand(5, 30);
            $unitCost = (float) $item->unit_cost;

            ProcurementRequest::create([
                'school_id' => $this->sid,
                'requested_by' => $storekeeper->user_id,
                'approved_by' => $i % 3 !== 0 ? $principal->user_id : null,
                'inventory_item_id' => $item->id,
                'item' => $item->name,
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'estimated_cost' => round($qty * $unitCost, 2),
                'actual_cost' => $i % 3 !== 0 ? round($qty * $unitCost * 1.02, 2) : null,
                'supplier' => 'Kilimanjaro General Supplies Ltd',
                'status' => $i % 3 === 0 ? 'pending' : 'completed',
                'threshold_flag' => $qty * $unitCost > 500000,
                'notes' => 'Restock request — running low.',
                'approved_at' => $i % 3 !== 0 ? now()->subDays(mt_rand(2, 20)) : null,
            ]);
        }

        // Stock requests — the front-line "we need more of this" flow.
        foreach ($items->take(4) as $i => $item) {
            StockRequest::create([
                'school_id' => $this->sid,
                'requested_by' => $staff[($i + 3) % $staff->count()]->user_id,
                'inventory_item_id' => $item->id,
                'item' => $item->name,
                'quantity' => mt_rand(2, 10),
                'reason' => 'Needed for upcoming term activities.',
                'status' => $i % 2 === 0 ? 'approved' : 'pending',
                'reviewed_by' => $i % 2 === 0 ? $storekeeper->user_id : null,
                'reviewed_at' => $i % 2 === 0 ? now()->subDays(3) : null,
            ]);
        }
    }

    // ─────────────────── Learning Profile & Counseling ───────────────────

    private function seedLearningProfileAndCounseling($students, $staff): void
    {
        $counselor = $staff->firstWhere('position', 'Academic Master') ?? $staff->first();

        // A small aptitude test bank.
        $questions = [];
        foreach ([
            ['If a train travels 60km in 45 minutes, what is its speed in km/h?', ['A' => '60', 'B' => '80', 'C' => '75', 'D' => '90'], 'B', 'Numerical'],
            ['Which word is the odd one out?', ['A' => 'Apple', 'B' => 'Banana', 'C' => 'Carrot', 'D' => 'Mango'], 'C', 'Verbal'],
            ['Complete the sequence: 2, 4, 8, 16, ?', ['A' => '24', 'B' => '32', 'C' => '20', 'D' => '18'], 'B', 'Numerical'],
            ['A rectangle has length 8cm and width 5cm. What is its area?', ['A' => '13', 'B' => '26', 'C' => '40', 'D' => '45'], 'C', 'Numerical'],
            ['Which shape completes the pattern?', ['A' => 'Circle', 'B' => 'Square', 'C' => 'Triangle', 'D' => 'Star'], 'A', 'Spatial'],
        ] as [$q, $options, $answer, $section]) {
            $questions[] = \App\Models\AptitudeQuestion::create([
                'school_id' => $this->sid,
                'question_text' => $q,
                'type' => 'mcq',
                'section' => $section,
                'options' => $options,
                'correct_answer' => $answer,
                'marks' => 10,
            ]);
        }

        // A handful of attempts.
        foreach ($students->random(min(20, $students->count())) as $student) {
            $attempt = \App\Models\AptitudeAttempt::create([
                'school_id' => $this->sid,
                'student_id' => $student->id,
                'counselor_id' => $counselor->user_id,
                'total_score' => 0,
                'time_taken' => mt_rand(600, 1800),
            ]);

            $total = 0;
            foreach ($questions as $question) {
                $correct = mt_rand(1, 100) <= 65;
                $obtained = $correct ? $question->marks : 0;
                $total += $obtained;

                \App\Models\AptitudeAnswer::create([
                    'school_id' => $this->sid,
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'student_answer' => $correct ? $question->correct_answer : 'A',
                    'obtained_marks' => $obtained,
                ]);
            }

            $attempt->update(['total_score' => $total]);
        }

        // Interest inventory for a sample of students.
        $interestAnswers = [
            'Enjoys working with numbers and solving logical problems.',
            'Prefers hands-on, practical activities over theory.',
            'Enjoys helping and working closely with other people.',
            'Comfortable speaking in front of groups.',
            'Prefers quiet, independent work.',
        ];
        foreach ($students->random(min(15, $students->count())) as $student) {
            $data = ['school_id' => $this->sid, 'student_id' => $student->id, 'created_by' => $counselor->user_id, 'date' => now()->subDays(mt_rand(5, 60))];
            for ($q = 1; $q <= 17; $q++) {
                $data['q'.$q] = $this->pick($interestAnswers);
            }
            InterestInventory::create($data);
        }

        // Classroom guidance sessions, one per class over a few weeks.
        foreach (SchoolClass::where('school_id', $this->sid)->get() as $class) {
            foreach (range(1, 3) as $week) {
                ClassroomGuidance::create([
                    'school_id' => $this->sid,
                    'class_id' => $class->id,
                    'date' => now()->subWeeks($week),
                    'tasks' => 'Study skills and time management guidance.',
                    'achievements' => 'Good class participation and engagement.',
                    'challenges' => 'A few students need one-on-one follow-up.',
                    'created_by' => $counselor->user_id,
                ]);
            }
        }

        // Counseling intake forms and individual session reports for a few students.
        foreach ($students->random(min(6, $students->count())) as $student) {
            CounselingIntakeForm::create([
                'school_id' => $this->sid,
                'student_id' => $student->id,
                'gender' => $student->gender,
                'age' => now()->diffInYears($student->date_of_birth),
                'stream' => optional($student->class)->name,
                'education_program' => 'O-Level',
                'g_performance' => 'Average to good academic performance.',
                'living_situation' => $student->dormitory_id ? 'Boarding' : 'Day scholar',
                'father_name' => 'Mzee '.$this->pick(DemoData::SURNAMES),
                'father_occupation' => $this->pick(DemoData::OCCUPATIONS),
                'father_age' => mt_rand(38, 58),
                'father_phone' => $this->phone(),
                'guardian_name' => $student->guardian?->first_name.' '.$student->guardian?->last_name,
                'guardian_relationship' => $student->guardian?->relation_to_student,
                'mother_name' => 'Mama '.$this->pick(DemoData::SURNAMES),
                'mother_occupation' => $this->pick(DemoData::OCCUPATIONS),
                'mother_age' => mt_rand(35, 55),
                'mother_phone' => $this->phone(),
                'parents_relationship' => 'Married',
                'siblings_brothers' => mt_rand(0, 3),
                'siblings_sisters' => mt_rand(0, 3),
                'birth_order' => $this->pick(['First born', 'Second born', 'Middle child', 'Last born']),
                'referred_by' => 'Class Teacher',
                'health_problems' => 'None reported.',
                'previous_counseling' => 'None.',
                'reason_for_counseling' => 'General wellbeing and academic adjustment check-in.',
                'chief_complaint' => 'Difficulty balancing studies and boarding life.',
                'understanding_of_services' => 'Understands counseling is confidential and supportive.',
                'counseling_type' => ['Individual'],
            ]);

            IndividualSessionReport::create([
                'school_id' => $this->sid,
                'student_id' => $student->id,
                'user_id' => $counselor->user_id,
                'date' => now()->subDays(mt_rand(3, 30)),
                'time' => '10:00:00',
                'session_number' => 1,
                'presenting_problem' => 'Academic adjustment and time-management concerns.',
                'work_done' => 'Discussed study routines and boarding life balance.',
                'assessment_progress' => 'Student is settling in well, showing improved routine.',
                'intervention_plan' => 'Weekly check-ins for the next month.',
                'follow_up' => 'Scheduled follow-up in two weeks.',
                'biopsychosocial_formulation' => 'No significant risk factors identified. Support network in place.',
            ]);
        }
    }

    // ─────────────────── Documents ───────────────────

    private function seedDocumentsAndSignatures($staff, AcademicSession $session, $classes): void
    {
        $principal = $staff->first();

        $documents = [
            ['School Prospectus '.now()->year, 'Prospectus', null],
            ['Form I Admission Guidelines', 'Admissions', null],
            ['Academic Calendar '.now()->year, 'Calendar', null],
            ['Form IV Mock Exam Timetable', 'Exams', $classes->firstWhere('level', 'Form IV')],
            ['Parents Handbook', 'Policy', null],
        ];

        foreach ($documents as [$title, $category, $class]) {
            $path = 'documents/'.$this->sid.'/'.Str::slug($title).'.pdf';

            Storage::disk('public')->put($path, $this->placeholderPdf($title, $this->school->name));

            Document::create([
                'school_id' => $this->sid,
                'title' => $title,
                'description' => $title.' for '.$this->school->name.'.',
                'category' => $category,
                'file_path' => $path,
                'original_name' => basename($path),
                'file_size' => Storage::disk('public')->size($path),
                'mime_type' => 'application/pdf',
                'uploaded_by' => $principal->user_id,
                'academic_session_id' => $session->id,
                'class_id' => $class?->id,
                'language' => 'English',
                'document_date' => now()->subDays(mt_rand(10, 90)),
                'author' => $this->school->name,
                'tags' => [$category],
                'download_count' => mt_rand(3, 60),
                'is_featured' => $category === 'Prospectus',
                'is_restricted' => false,
            ]);
        }

        // A signed document — the school's own signature/verification ledger.
        $content = 'Form IV Mock Exam Timetable — '.$this->school->name;

        \App\Models\DocumentSignature::create([
            'school_id' => $this->sid,
            'code' => strtoupper(Str::random(10)),
            'doc_type' => 'exam_timetable',
            'title' => 'Form IV Mock Exam Timetable',
            'summary' => 'Digitally verified exam timetable for Form IV mock examinations.',
            'content_hash' => hash('sha256', $content),
            'signed_by' => $principal->user_id,
        ]);
    }

    // ─────────────────── Pocket money ───────────────────

    private function seedPocketMoney($students, $staff): void
    {
        $accountant = $staff[15] ?? $staff->last();

        foreach ($students->random(min(30, $students->count())) as $student) {
            $balance = 0;

            foreach (range(1, mt_rand(2, 4)) as $n) {
                $isDeposit = $n === 1 || mt_rand(1, 100) <= 70;
                $amount = $isDeposit ? mt_rand(5, 30) * 1000 : mt_rand(1, 10) * 1000;
                $amount = min($amount, $isDeposit ? $amount : $balance);

                if (! $isDeposit && $balance <= 0) {
                    continue;
                }

                $balance += $isDeposit ? $amount : -$amount;

                PocketTransaction::create([
                    'school_id' => $this->sid,
                    'student_id' => $student->id,
                    'type' => $isDeposit ? 'deposit' : 'withdrawal',
                    'amount' => $amount,
                    'balance_after' => $balance,
                    'performed_by' => $accountant->user_id,
                    'note' => $isDeposit ? 'Pocket money deposit from guardian.' : 'Withdrawal for personal use.',
                ]);
            }
        }
    }

    // ─────────────────── Dormitory allocations ───────────────────

    /**
     * Proper allocation records for the beds DemoSchoolSeeder already marked
     * occupied — that seeder set bed status directly for speed, but the
     * allocation-history screen reads from this table, not the bed's status
     * column alone.
     */
    private function seedDormitoryAllocations($dormitories, AcademicSession $session, $staff): void
    {
        $dormMaster = $staff->firstWhere('position', 'Dorm Master') ?? $staff->last();

        $occupiedBeds = DormitoryBed::where('status', 'occupied')
            ->whereNotNull('current_student_id')
            ->get();

        $rows = [];
        $now = now();

        foreach ($occupiedBeds as $bed) {
            $rows[] = [
                'school_id' => $this->sid,
                'bed_id' => $bed->id,
                'student_id' => $bed->current_student_id,
                'academic_session_id' => $session->id,
                'allocation_date' => $session->start_date,
                'start_date' => $session->start_date,
                'end_date' => null,
                'status' => 'active',
                'notes' => null,
                'allocated_by' => $dormMaster->user_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('dormitory_bed_allocations')->insert($chunk);
        }
    }

    // ─────────────────── Daily reports & lesson plans ───────────────────

    private function seedDailyReportsAndLessonPlans($staff, AcademicSession $session, $classes, $subjects): void
    {
        $teachers = $staff->slice(4, 10)->values();

        foreach ($teachers as $teacher) {
            foreach (range(1, 5) as $daysAgo) {
                $report = DailyReport::create([
                    'school_id' => $this->sid,
                    'teacher_id' => $teacher->user_id,
                    'report_date' => now()->subDays($daysAgo),
                    'status' => 'submitted',
                    'summary' => 'Covered scheduled lessons and supervised prep time.',
                    'challenges' => $daysAgo % 3 === 0 ? 'A few students needed extra support with homework.' : null,
                    'next_day_plan' => 'Continue with the next topic in the scheme of work.',
                    'submitted_at' => now()->subDays($daysAgo)->setTime(16, 30),
                ]);

                \App\Models\DailyReportActivity::create([
                    'school_id' => $this->sid,
                    'daily_report_id' => $report->id,
                    'type' => 'duty',
                    'title' => 'Lesson delivery',
                    'description' => 'Regular scheduled teaching periods.',
                    'time_from' => '08:00:00',
                    'time_to' => '14:20:00',
                ]);
            }
        }

        // subject_class carries no school_id column of its own, so it must be
        // scoped explicitly by class_id — an unfiltered query here would pull
        // other schools' rows (and their staff ids) right alongside ours.
        // Its teacher_id is a staff.id, but lesson_plans.teacher_id and
        // lesson_subtopics.covered_by both reference users.id, so each row is
        // translated through staff.
        $assignments = DB::table('subject_class')->whereIn('class_id', $classes->pluck('id'))->get();
        $staffUserIdById = $staff->pluck('user_id', 'id');

        foreach ($assignments->take(10) as $assignment) {
            $teacherUserId = $staffUserIdById->get($assignment->teacher_id);

            $plan = LessonPlan::create([
                'school_id' => $this->sid,
                'academic_session_id' => $session->id,
                'subject_id' => $assignment->subject_id,
                'class_id' => $assignment->class_id,
                'teacher_id' => $teacherUserId,
                'title' => 'Term Scheme of Work',
                'description' => 'Lesson sequence for the current term.',
            ]);

            foreach (['Introduction & Foundations', 'Core Concepts', 'Applications', 'Review & Assessment'] as $i => $topicTitle) {
                $topic = \App\Models\LessonTopic::create([
                    'school_id' => $this->sid,
                    'lesson_plan_id' => $plan->id,
                    'title' => $topicTitle,
                    'order_no' => $i + 1,
                ]);

                foreach (range(1, 2) as $j) {
                    $covered = $i < 2;

                    \App\Models\LessonSubtopic::create([
                        'school_id' => $this->sid,
                        'lesson_topic_id' => $topic->id,
                        'title' => $topicTitle.' — Part '.$j,
                        'order_no' => $j,
                        'status' => $covered ? 'covered' : 'pending',
                        'date_covered' => $covered ? now()->subDays(mt_rand(5, 40)) : null,
                        'covered_by' => $covered ? $teacherUserId : null,
                    ]);
                }
            }
        }
    }

    // ─────────────────── Accountant assignments ───────────────────

    private function seedAccountantAssignments($staff, $classes): void
    {
        $accountant = $staff->firstWhere('role', 'accountant');

        if (! $accountant) {
            return;
        }

        foreach ($classes->take(4) as $class) {
            DB::table('accountant_class_assignments')->insert([
                'school_id' => $this->sid,
                'user_id' => $accountant->user_id,
                'class_id' => $class->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // ─────────────────── Tasks ───────────────────

    private function seedTaskLogs($staff): void
    {
        $principal = $staff->first();

        $descriptions = [
            'Prepare and submit the termly academic performance report',
            'Complete the annual budget proposal for the department',
            'Update the student attendance register for the term',
            'Review and approve pending procurement requests',
            'Conduct classroom observation for two junior teachers',
            'Finalise the Form IV mock examination results',
            'Update the school website with the new term calendar',
            'Coordinate transport arrangements for the sports day',
        ];

        foreach ($staff->slice(1, 8) as $i => $member) {
            $deadline = now()->addDays(mt_rand(-15, 20));
            $status = match (true) {
                $deadline->isPast() && $i % 4 === 0 => 'overdue',
                $deadline->isPast() => 'approved',
                $i % 3 === 0 => 'in_progress',
                default => 'pending',
            };

            \App\Models\TaskLog::create([
                'school_id' => $this->sid,
                'user_id' => $member->user_id,
                'role' => $member->role,
                'task_description' => $descriptions[$i % count($descriptions)],
                'deadline' => $deadline,
                'percent_complete' => match ($status) {
                    'approved' => 100,
                    'overdue' => mt_rand(20, 70),
                    'in_progress' => mt_rand(30, 80),
                    default => 0,
                },
                'status' => $status,
                'submitted_at' => in_array($status, ['approved'], true) ? $deadline->copy()->subDay() : null,
                'approved_by' => $status === 'approved' ? $principal->user_id : null,
                'approved_at' => $status === 'approved' ? $deadline : null,
                'is_flagged_compliance' => $status === 'overdue',
                'is_flagged_exceeds' => false,
            ]);
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

    /** A minimal, valid single-page PDF — enough for a browser to open and display it. */
    private function placeholderPdf(string $title, string $subtitle): string
    {
        $stream = "BT /F1 18 Tf 50 700 Td ({$title}) Tj ET BT /F1 12 Tf 50 670 Td ({$subtitle}) Tj ET";
        $streamLen = strlen($stream);

        return <<<PDF
%PDF-1.4
1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj
2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj
3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Resources<</Font<</F1 4 0 R>>>>/Contents 5 0 R>>endobj
4 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj
5 0 obj<</Length {$streamLen}>>stream
{$stream}
endstream
endobj
xref
0 6
0000000000 65535 f
trailer<</Size 6/Root 1 0 R>>
startxref
0
%%EOF
PDF;
    }
}
