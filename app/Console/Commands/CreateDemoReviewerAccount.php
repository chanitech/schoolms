<?php

namespace App\Console\Commands;

use App\Models\AcademicSession;
use App\Models\Bill;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\Mark;
use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateDemoReviewerAccount extends Command
{
    protected $signature = 'demo:reviewer
        {--school= : School slug (defaults to the first school)}
        {--phone=0700000001 : Demo guardian phone number}
        {--password=ReviewDemo2026 : Demo guardian password}
        {--fresh : Reset the demo password and regenerate marks/fees}';

    protected $description = 'Create an isolated, clearly-labelled demo guardian account (with a demo student, marks, and fees) for app store reviewers. Idempotent — safe to run more than once.';

    public function handle(): int
    {
        $school = $this->option('school')
            ? School::withoutGlobalScopes()->where('slug', $this->option('school'))->first()
            : School::withoutGlobalScopes()->orderBy('id')->first();

        if (! $school) {
            $this->error('No school found.');
            return self::FAILURE;
        }

        // Bind tenant so BelongsToSchool scopes/auto-fill behave as in a request
        app()->instance('currentSchool', $school);

        $session = AcademicSession::where('is_current', true)->first()
            ?? AcademicSession::latest('id')->first();
        $exam = Exam::where('status', 'published')->latest('id')->first();

        if (! $session) {
            $this->error('No academic session found.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($school, $session, $exam) {
            // Own class so the demo student never appears in a real class's
            // lists or result rankings.
            $class = SchoolClass::firstOrCreate(
                ['name' => 'Demo Class (App Review)', 'school_id' => $school->id],
                ['level' => 'Demo', 'section' => null, 'capacity' => 1]
            );

            $student = Student::firstOrCreate(
                ['admission_no' => 'DEMO-0001', 'school_id' => $school->id],
                [
                    'first_name' => 'Demo', 'middle_name' => 'Review', 'last_name' => 'Student',
                    'gender' => 'male', 'date_of_birth' => '2010-01-01',
                    'class_id' => $class->id, 'academic_session_id' => $session->id,
                    'admission_date' => now()->toDateString(), 'status' => 'active',
                ]
            );

            $phone = preg_replace('/[\s\-\(\)]+/', '', $this->option('phone'));

            $user = User::withoutSchoolScope()->where('phone', $phone)->first();
            if (! $user) {
                $user = new User();
                $user->forceFill([
                    'name' => 'Demo Parent (App Review)',
                    'first_name' => 'Demo', 'last_name' => 'Parent',
                    'email' => 'demo.parent.review@schoolms.local',
                    'phone' => $phone,
                    'password' => Hash::make($this->option('password')),
                    'school_id' => $school->id,
                ])->save();
            } elseif ($this->option('fresh')) {
                $user->forceFill(['password' => Hash::make($this->option('password'))])->save();
            }
            if (! $user->hasRole('guardian')) {
                $user->assignRole('guardian');
            }

            $guardian = Guardian::firstOrCreate(
                ['user_id' => $user->id, 'school_id' => $school->id],
                [
                    'first_name' => 'Demo', 'last_name' => 'Parent', 'gender' => 'male',
                    'relation_to_student' => 'father', 'phone' => $phone,
                ]
            );

            $student->update(['guardian_id' => $guardian->id]);

            // Marks in the latest published exam (demo class only, so real
            // rankings are untouched)
            if ($exam) {
                $subjects = Subject::orderBy('id')->take(7)->get();
                $grades   = Grade::all();
                $sample   = [88, 76, 91, 67, 82, 74, 79];

                foreach ($subjects->values() as $i => $subject) {
                    $markValue = $sample[$i % count($sample)];
                    $grade = $grades->first(fn ($g) => $markValue >= $g->min_mark && $markValue <= $g->max_mark);

                    Mark::updateOrCreate(
                        [
                            'student_id' => $student->id, 'subject_id' => $subject->id,
                            'exam_id' => $exam->id, 'school_id' => $school->id,
                        ],
                        [
                            'class_id' => $student->class_id,
                            'academic_session_id' => $exam->academic_session_id ?? $session->id,
                            'mark' => $markValue, 'grade_id' => $grade?->id,
                        ]
                    );
                }
            }

            // A fee bill with a partial payment, so the fees page and a
            // receipt have something to show
            $bill = Bill::firstOrCreate(
                ['title' => 'Demo School Fees (App Review)', 'school_id' => $school->id],
                [
                    'class_id' => $student->class_id, 'description' => 'Demo bill for app store review only',
                    'amount' => 500000, 'academic_session_id' => $session->id,
                    'due_date' => now()->addMonths(2)->toDateString(), 'status' => 'open',
                ]
            );

            $studentBill = StudentBill::firstOrCreate(
                ['bill_id' => $bill->id, 'student_id' => $student->id, 'school_id' => $school->id],
                [
                    'total_amount' => 500000, 'amount_paid' => 300000, 'balance' => 200000,
                    'status' => 'partial', 'due_date' => $bill->due_date,
                ]
            );

            Payment::firstOrCreate(
                ['reference' => 'DEMO-RCPT-0001', 'school_id' => $school->id],
                [
                    'student_id' => $student->id, 'student_bill_id' => $studentBill->id,
                    'class_id' => $student->class_id, 'amount' => 300000, 'method' => 'cash',
                    'payment_date' => now()->toDateString(), 'paid_at' => now(),
                    'status' => 'verified', 'note' => 'Demo payment for app store review only',
                ]
            );
        });

        $this->info('Demo reviewer account ready:');
        $this->table(['Field', 'Value'], [
            ['School code', $school->slug],
            ['Phone', $this->option('phone')],
            ['Password', $this->option('password')],
            ['Student', 'Demo Review Student (DEMO-0001)'],
            ['Login URL', 'https://schoolms.chanitech.co.tz/guardian/login'],
        ]);
        $this->warn('Provide these in Play Console → App content → App access. Delete the demo data after review if desired.');

        return self::SUCCESS;
    }
}
