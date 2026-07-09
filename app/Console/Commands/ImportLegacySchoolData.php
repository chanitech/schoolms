<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * One-time import of the old single-tenant Kitungwa database (kassapp) into
 * this app's new multi-tenant schema, tagging every row with school_id.
 *
 * Setup:
 *   1. In .env, set LEGACY_DB_HOST / LEGACY_DB_PORT / LEGACY_DB_DATABASE /
 *      LEGACY_DB_USERNAME / LEGACY_DB_PASSWORD to point at the old database
 *      (see the 'legacy' connection added in config/database.php).
 *   2. Run `php artisan legacy:import --audit` and review the flagged rows.
 *      Add any ids you want excluded to the arrays below.
 *   3. Run `php artisan legacy:import --dry-run` to see counts without
 *      writing anything (runs inside a transaction that gets rolled back).
 *   4. Run `php artisan legacy:import` for real.
 *
 * Assumptions baked in (confirmed with the project owner):
 *   - budgets.status / budget_items.status enums were widened in migration
 *     2026_07_04_120000_widen_columns_for_legacy_import.php to fit all old
 *     values (no lossy mapping).
 *   - enrollments.roll_no was widened back to VARCHAR(20) in that same
 *     migration to preserve values like "F1--001".
 *   - Old roles without a new equivalent (Director, Assistant Director, DO,
 *     Librarian, Coordinator, Academic, HOD, Doctor, Counselor) fall back to
 *     the generic "Staff" role. Old role "guardian" is dropped entirely (the
 *     new schema has no guardian RBAC role; guardian portal access is via
 *     guardians.user_id).
 *   - Legacy exams are marked status="published" since they already have
 *     real marks recorded against them.
 */
class ImportLegacySchoolData extends Command
{
    protected $signature = 'legacy:import
        {--school=1 : ID of the school (in the new DB) all imported rows belong to}
        {--dry-run : Wrap everything in a transaction and roll back at the end}
        {--audit : List legacy users/staff/guardians with a junk-data heuristic flag, then exit}';

    protected $description = 'Import the old single-tenant school database into this app\'s multi-tenant schema';

    // Run --audit first, review the output, then add any extra ids you want
    // excluded here before the real run.
    // 66 "fewfdfdg dfsdfds" and 69 "dds cxzc" are keyboard-mash guardian
    // portal test signups the heuristic didn't catch (confirmed via audit).
    protected array $excludeUserIds = [66, 69];
    protected array $excludeStaffIds = [];
    protected array $excludeGuardianIds = [];

    private int $schoolId;

    // Track which legacy primary keys were actually inserted, so downstream
    // tables can null-out (optional FK) or skip (required FK) rows that
    // point at something we didn't migrate.
    private array $userIds = [];
    private array $staffIds = [];
    private array $departmentIds = [];
    private array $classIds = [];
    private array $dormitoryIds = [];
    private array $dormitoryRoomIds = [];
    private array $dormitoryBedIds = [];
    private array $academicSessionIds = [];
    private array $guardianIds = [];
    private array $subjectIds = [];
    private array $studentIds = [];
    private array $examIds = [];
    private array $gradeIds = [];
    private array $bookIds = [];
    private array $categoryIds = [];
    private array $billIds = [];
    private array $studentBillIds = [];
    private array $budgetIds = [];
    private array $budgetItemIds = [];
    private array $groupReportIds = [];
    private array $aptitudeQuestionIds = [];
    private array $aptitudeAttemptIds = [];
    private array $loanCategoryIds = [];
    private array $loanIds = [];
    private array $roleIdByName = [];

    private array $stats = [];

    public function handle(): int
    {
        if (! $this->confirmLegacyConnection()) {
            return self::FAILURE;
        }

        if ($this->option('audit')) {
            $this->runAudit();

            return self::SUCCESS;
        }

        $this->schoolId = (int) $this->option('school');
        $dryRun = (bool) $this->option('dry-run');

        if (! DB::table('schools')->where('id', $this->schoolId)->exists()) {
            $this->error("No school with id={$this->schoolId} exists in the new database. Create it first.");

            return self::FAILURE;
        }

        $this->loadRoleMap();

        $this->info('Importing legacy data into school_id='.$this->schoolId.($dryRun ? ' (DRY RUN)' : ''));

        DB::beginTransaction();

        try {
            $this->importDepartments();
            $this->importUsers();
            $this->importStaff();
            $this->linkDepartmentHeads();
            $this->importAcademicSessions();
            $this->importSchoolClasses();
            $this->importDormitories();
            $this->importDormitoryRooms();
            $this->importDormitoryBeds();
            $this->importDivisions();
            $this->importGrades();
            $this->importGuardians();
            $this->importSubjects();
            $this->importSubjectClass();
            $this->importSubjectTeacher();
            $this->importStudents();
            $this->linkDormitoryBedOccupants();
            $this->importEnrollments();
            $this->importStudentSubject();
            $this->importDormitoryBedAllocations();
            $this->importExams();
            $this->importMarks();
            $this->importStudentResults();
            $this->importAttendances();
            $this->importLeaves();
            $this->importEvents();
            $this->importCategories();
            $this->importBooks();
            $this->importLendings();
            $this->importBills();
            $this->importStudentBills();
            $this->importPayments();
            $this->importPocketTransactions();
            $this->importBudgets();
            $this->importBudgetItems();
            $this->importInvoices();
            $this->importJobCards();
            $this->importClassroomGuidances();
            $this->importCounselingIntakeForms();
            $this->importIndividualSessionReports();
            $this->importGroupCounselingSessionReports();
            $this->importGroupCounselingStudent();
            $this->importInterestInventories();
            $this->importAptitudeQuestions();
            $this->importAptitudeAttempts();
            $this->importAptitudeAnswers();
            $this->importLoanCategories();
            $this->importLoans();
            $this->importLoanRepayments();
            $this->importBankStatements();
            $this->importStaffSalaryHistory();
            $this->importModelHasRoles();

            if ($dryRun) {
                DB::rollBack();
                $this->info('Dry run complete — nothing was written.');
            } else {
                DB::commit();
                $this->info('Import committed.');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            $this->error('Import aborted and rolled back: '.$e->getMessage());
            $this->error($e->getFile().':'.$e->getLine());

            return self::FAILURE;
        }

        $this->printReport();

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------
    // Core reference data
    // -----------------------------------------------------------------

    private function importDepartments(): void
    {
        foreach (DB::connection('legacy')->table('departments')->get() as $row) {
            $this->importRow('departments', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'name' => $row->name,
                'description' => $row->description,
                'head_id' => null, // linked in linkDepartmentHeads() once staff exists
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], $this->departmentIds);
        }
    }

    private function importUsers(): void
    {
        foreach (DB::connection('legacy')->table('users')->orderBy('id')->get() as $row) {
            if (in_array($row->id, $this->excludeUserIds, true) || $this->looksLikeJunk($row->first_name, $row->last_name)) {
                $this->skipJunk('users');
                continue;
            }

            $this->importRow('users', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'is_super_admin' => 0,
                'name' => $row->name,
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'email' => $row->email,
                'phone' => $row->phone,
                'email_verified_at' => $row->email_verified_at,
                'password' => $row->password,
                'remember_token' => $row->remember_token,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'department_id' => $this->fkOrNull($row->department_id, $this->departmentIds),
                'position' => $row->position,
                'photo' => $row->photo,
                'role' => $row->role ?? 'Staff',
            ], $this->userIds);
        }
    }

    private function importStaff(): void
    {
        foreach (DB::connection('legacy')->table('staff')->orderBy('id')->get() as $row) {
            if (in_array($row->id, $this->excludeStaffIds, true) || $this->looksLikeJunk($row->first_name, $row->last_name)) {
                $this->skipJunk('staff');
                continue;
            }

            $this->importRow('staff', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'user_id' => $this->fkOrNull($row->user_id, $this->userIds),
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'email' => $row->email,
                'phone' => $row->phone,
                'department_id' => $this->fkOrNull($row->department_id, $this->departmentIds),
                'position' => $row->position,
                'basic_salary' => $row->basic_salary,
                'hire_date' => $row->hire_date,
                'photo' => $row->photo,
                'role' => $row->role ?? 'Staff',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ], $this->staffIds);
        }
    }

    private function linkDepartmentHeads(): void
    {
        foreach (DB::connection('legacy')->table('departments')->whereNotNull('head_id')->get() as $row) {
            if (! isset($this->departmentIds[$row->id]) || ! isset($this->staffIds[$row->head_id])) {
                continue;
            }
            DB::table('departments')->where('id', $row->id)->update(['head_id' => $row->head_id]);
        }
    }

    private function importAcademicSessions(): void
    {
        foreach (DB::connection('legacy')->table('academic_sessions')->get() as $row) {
            $this->importRow('academic_sessions', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'name' => $row->name,
                'start_date' => $row->start_date,
                'end_date' => $row->end_date,
                'is_current' => $row->is_current,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ], $this->academicSessionIds);
        }
    }

    private function importSchoolClasses(): void
    {
        foreach (DB::connection('legacy')->table('school_classes')->get() as $row) {
            $this->importRow('school_classes', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'name' => $row->name,
                'level' => $row->level,
                'section' => $row->section,
                'capacity' => $row->capacity,
                'class_teacher_id' => $this->fkOrNull($row->class_teacher_id, $this->staffIds),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ], $this->classIds);
        }
    }

    private function importDormitories(): void
    {
        foreach (DB::connection('legacy')->table('dormitories')->get() as $row) {
            $this->importRow('dormitories', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'name' => $row->name,
                'capacity' => $row->capacity,
                'gender' => $row->gender,
                'dorm_master_id' => $this->fkOrNull($row->dorm_master_id, $this->staffIds),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ], $this->dormitoryIds);
        }
    }

    private function importDormitoryRooms(): void
    {
        foreach (DB::connection('legacy')->table('dormitory_rooms')->get() as $row) {
            if (! isset($this->dormitoryIds[$row->dormitory_id])) {
                $this->skipMissingFk('dormitory_rooms');
                continue;
            }
            $this->importRow('dormitory_rooms', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'dormitory_id' => $row->dormitory_id,
                'room_number' => $row->room_number,
                'floor' => $row->floor,
                'capacity' => $row->capacity,
                'occupied_beds' => $row->occupied_beds,
                'room_type' => $row->room_type,
                'has_attached_bathroom' => $row->has_attached_bathroom,
                'has_balcony' => $row->has_balcony,
                'is_available' => $row->is_available,
                'facilities' => $row->facilities,
                'description' => $row->description,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ], $this->dormitoryRoomIds);
        }
    }

    private function importDormitoryBeds(): void
    {
        foreach (DB::connection('legacy')->table('dormitory_beds')->get() as $row) {
            if (! isset($this->dormitoryRoomIds[$row->room_id])) {
                $this->skipMissingFk('dormitory_beds');
                continue;
            }
            $this->importRow('dormitory_beds', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'room_id' => $row->room_id,
                'bed_number' => $row->bed_number,
                'bed_type' => $row->bed_type,
                'status' => $row->status,
                'current_student_id' => null, // linked in linkDormitoryBedOccupants() once students exist
                'features' => $row->features,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ], $this->dormitoryBedIds);
        }
    }

    private function importDivisions(): void
    {
        foreach (DB::connection('legacy')->table('divisions')->get() as $row) {
            $this->importRow('divisions', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'name' => $row->name,
                'min_points' => $row->min_points,
                'max_points' => $row->max_points,
                'description' => $row->description,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ]);
        }
    }

    private function importGrades(): void
    {
        foreach (DB::connection('legacy')->table('grades')->get() as $row) {
            $this->importRow('grades', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'name' => $row->name,
                'min_mark' => $row->min_mark,
                'max_mark' => $row->max_mark,
                'point' => $row->point,
                'description' => $row->description,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ], $this->gradeIds);
        }
    }

    private function importGuardians(): void
    {
        foreach (DB::connection('legacy')->table('guardians')->orderBy('id')->get() as $row) {
            if (in_array($row->id, $this->excludeGuardianIds, true) || $this->looksLikeJunk($row->first_name, $row->last_name)) {
                $this->skipJunk('guardians');
                continue;
            }

            $this->importRow('guardians', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'gender' => $row->gender,
                'relation_to_student' => $row->relation_to_student,
                'phone' => $row->phone,
                'email' => $row->email,
                'address' => $row->address,
                'occupation' => $row->occupation,
                'national_id' => $row->national_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'user_id' => $this->fkOrNull($row->user_id, $this->userIds),
            ], $this->guardianIds);
        }
    }

    // -----------------------------------------------------------------
    // Academics
    // -----------------------------------------------------------------

    private function importSubjects(): void
    {
        foreach (DB::connection('legacy')->table('subjects')->get() as $row) {
            $this->importRow('subjects', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'name' => $row->name,
                'code' => $row->code,
                'type' => $row->type,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
                'teacher_id' => $this->fkOrNull($row->teacher_id, $this->userIds),
                'department_id' => $this->fkOrNull($row->department_id, $this->departmentIds),
            ], $this->subjectIds);
        }
    }

    private function importSubjectClass(): void
    {
        foreach (DB::connection('legacy')->table('subject_class')->get() as $row) {
            if (! isset($this->subjectIds[$row->subject_id]) || ! isset($this->classIds[$row->class_id])) {
                $this->skipMissingFk('subject_class');
                continue;
            }
            $this->importRow('subject_class', [
                'id' => $row->id,
                'subject_id' => $row->subject_id,
                'class_id' => $row->class_id,
                'teacher_id' => $this->fkOrNull($row->teacher_id, $this->staffIds),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importSubjectTeacher(): void
    {
        foreach (DB::connection('legacy')->table('subject_teacher')->get() as $row) {
            if (! isset($this->subjectIds[$row->subject_id]) || ! isset($this->staffIds[$row->staff_id])) {
                $this->skipMissingFk('subject_teacher');
                continue;
            }
            $this->importRow('subject_teacher', [
                'id' => $row->id,
                'subject_id' => $row->subject_id,
                'staff_id' => $row->staff_id,
                'class_id' => $this->fkOrNull($row->class_id, $this->classIds),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importStudents(): void
    {
        foreach (DB::connection('legacy')->table('students')->orderBy('id')->get() as $row) {
            $this->importRow('students', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'admission_no' => $row->admission_no,
                'first_name' => $row->first_name,
                'middle_name' => $row->middle_name,
                'last_name' => $row->last_name,
                'gender' => $row->gender,
                'date_of_birth' => $row->date_of_birth,
                'national_id' => $row->national_id,
                'photo' => $row->photo,
                'guardian_id' => $this->fkOrNull($row->guardian_id, $this->guardianIds),
                'class_id' => $this->fkOrNull($row->class_id, $this->classIds),
                'dormitory_id' => $this->fkOrNull($row->dormitory_id, $this->dormitoryIds),
                'academic_session_id' => $this->fkOrNull($row->academic_session_id, $this->academicSessionIds),
                'admission_date' => $row->admission_date,
                'status' => $row->status,
                'address' => $row->address,
                'phone' => $row->phone,
                'email' => $row->email,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ], $this->studentIds);
        }
    }

    private function linkDormitoryBedOccupants(): void
    {
        foreach (DB::connection('legacy')->table('dormitory_beds')->whereNotNull('current_student_id')->get() as $row) {
            if (! isset($this->dormitoryBedIds[$row->id]) || ! isset($this->studentIds[$row->current_student_id])) {
                continue;
            }
            DB::table('dormitory_beds')->where('id', $row->id)->update(['current_student_id' => $row->current_student_id]);
        }
    }

    private function importEnrollments(): void
    {
        foreach (DB::connection('legacy')->table('enrollments')->get() as $row) {
            if (! isset($this->studentIds[$row->student_id]) || ! isset($this->classIds[$row->class_id]) || ! isset($this->academicSessionIds[$row->academic_session_id])) {
                $this->skipMissingFk('enrollments');
                continue;
            }
            $this->importRow('enrollments', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'student_id' => $row->student_id,
                'class_id' => $row->class_id,
                'academic_session_id' => $row->academic_session_id,
                'roll_no' => $row->roll_no,
                'status' => $row->status,
                'remarks' => $row->remarks,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importStudentSubject(): void
    {
        foreach (DB::connection('legacy')->table('student_subject')->cursor() as $row) {
            if (! isset($this->studentIds[$row->student_id]) || ! isset($this->subjectIds[$row->subject_id])) {
                $this->skipMissingFk('student_subject');
                continue;
            }
            $this->importRow('student_subject', [
                'id' => $row->id,
                'student_id' => $row->student_id,
                'subject_id' => $row->subject_id,
                'withdrawn' => $row->withdrawn,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importDormitoryBedAllocations(): void
    {
        foreach (DB::connection('legacy')->table('dormitory_bed_allocations')->get() as $row) {
            if (! isset($this->dormitoryBedIds[$row->bed_id]) || ! isset($this->studentIds[$row->student_id])
                || ! isset($this->academicSessionIds[$row->academic_session_id]) || ! isset($this->userIds[$row->allocated_by])) {
                $this->skipMissingFk('dormitory_bed_allocations');
                continue;
            }
            $this->importRow('dormitory_bed_allocations', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'bed_id' => $row->bed_id,
                'student_id' => $row->student_id,
                'academic_session_id' => $row->academic_session_id,
                'allocation_date' => $row->allocation_date,
                'start_date' => $row->start_date,
                'end_date' => $row->end_date,
                'status' => $row->status,
                'notes' => $row->notes,
                'allocated_by' => $row->allocated_by,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ]);
        }
    }

    private function importExams(): void
    {
        foreach (DB::connection('legacy')->table('exams')->get() as $row) {
            if (! isset($this->academicSessionIds[$row->academic_session_id])) {
                $this->skipMissingFk('exams');
                continue;
            }
            $this->importRow('exams', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'name' => $row->name,
                'term' => $row->term,
                'academic_session_id' => $row->academic_session_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
                'is_annual_exam' => $row->is_annual_exam,
                'status' => 'published', // legacy exams already have real marks recorded against them
                'reviewed_by' => null,
                'reviewed_at' => null,
                'published_by' => null,
                'published_at' => null,
            ], $this->examIds);
        }
    }

    private function importMarks(): void
    {
        foreach (DB::connection('legacy')->table('marks')->cursor() as $row) {
            if (! isset($this->studentIds[$row->student_id]) || ! isset($this->subjectIds[$row->subject_id])
                || ! isset($this->examIds[$row->exam_id]) || ! isset($this->academicSessionIds[$row->academic_session_id])) {
                $this->skipMissingFk('marks');
                continue;
            }
            $this->importRow('marks', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'student_id' => $row->student_id,
                'subject_id' => $row->subject_id,
                'exam_id' => $row->exam_id,
                'academic_session_id' => $row->academic_session_id,
                'mark' => $row->mark,
                'grade_id' => $this->fkOrNull($row->grade_id, $this->gradeIds),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ]);
        }
    }

    private function importStudentResults(): void
    {
        foreach (DB::connection('legacy')->table('student_results')->get() as $row) {
            if (! isset($this->studentIds[$row->student_id]) || ! isset($this->examIds[$row->exam_id])) {
                $this->skipMissingFk('student_results');
                continue;
            }
            $this->importRow('student_results', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'student_id' => $row->student_id,
                'exam_id' => $row->exam_id,
                'gpa' => $row->gpa,
                'total_points' => $row->total_points,
                'division' => $row->division,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    // -----------------------------------------------------------------
    // HR
    // -----------------------------------------------------------------

    private function importAttendances(): void
    {
        foreach (DB::connection('legacy')->table('attendances')->get() as $row) {
            if (! isset($this->staffIds[$row->staff_id])) {
                $this->skipMissingFk('attendances');
                continue;
            }
            $this->importRow('attendances', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'staff_id' => $row->staff_id,
                'date' => $row->date,
                'status' => $row->status,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importLeaves(): void
    {
        foreach (DB::connection('legacy')->table('leaves')->get() as $row) {
            if (! isset($this->staffIds[$row->staff_id])) {
                $this->skipMissingFk('leaves');
                continue;
            }
            $this->importRow('leaves', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'staff_id' => $row->staff_id,
                'requested_to' => $this->fkOrNull($row->requested_to, $this->staffIds),
                'type' => $row->type,
                'reason' => $row->reason,
                'start_date' => $row->start_date,
                'end_date' => $row->end_date,
                'status' => $row->status,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importEvents(): void
    {
        foreach (DB::connection('legacy')->table('events')->get() as $row) {
            if (! isset($this->staffIds[$row->created_by])) {
                $this->skipMissingFk('events');
                continue;
            }
            $this->importRow('events', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'title' => $row->title,
                'department_id' => $this->fkOrNull($row->department_id, $this->departmentIds),
                'type' => $row->type,
                'start_date' => $row->start_date,
                'end_date' => $row->end_date,
                'description' => $row->description,
                'created_by' => $row->created_by,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importJobCards(): void
    {
        foreach (DB::connection('legacy')->table('job_cards')->get() as $row) {
            if (! isset($this->staffIds[$row->assigned_by]) || ! isset($this->staffIds[$row->assigned_to])) {
                $this->skipMissingFk('job_cards');
                continue;
            }
            $this->importRow('job_cards', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'title' => $row->title,
                'description' => $row->description,
                'assigned_by' => $row->assigned_by,
                'assigned_to' => $row->assigned_to,
                'status' => $row->status,
                'rating' => $row->rating,
                'due_date' => $row->due_date,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importStaffSalaryHistory(): void
    {
        foreach (DB::connection('legacy')->table('staff_salary_history')->get() as $row) {
            if (! isset($this->staffIds[$row->staff_id]) || ! isset($this->userIds[$row->changed_by])) {
                $this->skipMissingFk('staff_salary_history');
                continue;
            }
            $this->importRow('staff_salary_history', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'staff_id' => $row->staff_id,
                'old_salary' => $row->old_salary,
                'new_salary' => $row->new_salary,
                'effective_date' => $row->effective_date,
                'changed_by' => $row->changed_by,
                'reason' => $row->reason,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importBankStatements(): void
    {
        foreach (DB::connection('legacy')->table('bank_statements')->get() as $row) {
            if (! isset($this->staffIds[$row->staff_id]) || ! isset($this->userIds[$row->uploaded_by])) {
                $this->skipMissingFk('bank_statements');
                continue;
            }
            $this->importRow('bank_statements', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'staff_id' => $row->staff_id,
                'file_path' => $row->file_path,
                'original_name' => $row->original_name,
                'mime_type' => $row->mime_type,
                'file_size' => $row->file_size,
                'statement_month' => $row->statement_month,
                'uploaded_by' => $row->uploaded_by,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    // -----------------------------------------------------------------
    // Library
    // -----------------------------------------------------------------

    private function importCategories(): void
    {
        foreach (DB::connection('legacy')->table('categories')->get() as $row) {
            $this->importRow('categories', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'name' => $row->name,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], $this->categoryIds);
        }
    }

    private function importBooks(): void
    {
        foreach (DB::connection('legacy')->table('books')->get() as $row) {
            $this->importRow('books', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'title' => $row->title,
                'author' => $row->author,
                'category_id' => $this->fkOrNull($row->category_id, $this->categoryIds),
                'isbn' => $row->isbn,
                'quantity' => $row->quantity,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], $this->bookIds);
        }
    }

    private function importLendings(): void
    {
        foreach (DB::connection('legacy')->table('lendings')->get() as $row) {
            if (! isset($this->bookIds[$row->book_id])) {
                $this->skipMissingFk('lendings');
                continue;
            }
            $borrowerExists = match ($row->borrower_type) {
                'App\\Models\\Student' => isset($this->studentIds[$row->user_id]),
                'App\\Models\\Staff' => isset($this->staffIds[$row->user_id]),
                default => true, // unknown polymorphic type — keep as-is rather than guess
            };
            if (! $borrowerExists) {
                $this->skipMissingFk('lendings');
                continue;
            }
            $this->importRow('lendings', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'book_id' => $row->book_id,
                'quantity' => $row->quantity,
                'user_id' => $row->user_id,
                'borrower_type' => $row->borrower_type,
                'lend_date' => $row->lend_date,
                'return_date' => $row->return_date,
                'returned_at' => $row->returned_at,
                'returned' => $row->returned,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    // -----------------------------------------------------------------
    // Finance
    // -----------------------------------------------------------------

    private function importBills(): void
    {
        foreach (DB::connection('legacy')->table('bills')->get() as $row) {
            if (! isset($this->classIds[$row->class_id])) {
                $this->skipMissingFk('bills');
                continue;
            }
            $this->importRow('bills', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'class_id' => $row->class_id,
                'title' => $row->title,
                'description' => $row->description,
                'amount' => $row->amount,
                'academic_session_id' => $this->fkOrNull($row->academic_session_id, $this->academicSessionIds),
                'due_date' => $row->due_date,
                'status' => $row->status,
                'created_by' => $this->fkOrNull($row->created_by, $this->userIds),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], $this->billIds);
        }
    }

    private function importStudentBills(): void
    {
        foreach (DB::connection('legacy')->table('student_bills')->get() as $row) {
            if (! isset($this->studentIds[$row->student_id])) {
                $this->skipMissingFk('student_bills');
                continue;
            }
            $this->importRow('student_bills', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'bill_id' => $this->fkOrNull($row->bill_id, $this->billIds),
                'student_id' => $row->student_id,
                'total_amount' => $row->total_amount,
                'amount_paid' => $row->amount_paid,
                'status' => $row->status,
                'due_date' => $row->due_date,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'balance' => $row->balance,
                'notes' => $row->notes,
            ], $this->studentBillIds);
        }
    }

    private function importPayments(): void
    {
        foreach (DB::connection('legacy')->table('payments')->get() as $row) {
            $this->importRow('payments', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'student_id' => $this->fkOrNull($row->student_id, $this->studentIds),
                'student_bill_id' => $this->fkOrNull($row->student_bill_id, $this->studentBillIds),
                'amount' => $row->amount,
                'method' => $row->payment_method,
                'reference' => $row->reference,
                'payment_date' => $row->payment_date,
                'received_by' => $this->fkOrNull($row->received_by, $this->userIds),
                'paid_at' => $row->paid_at,
                'note' => $row->note,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'recorded_by' => $this->fkOrNull($row->recorded_by, $this->userIds),
            ]);
        }
    }

    private function importPocketTransactions(): void
    {
        foreach (DB::connection('legacy')->table('pocket_transactions')->get() as $row) {
            if (! isset($this->studentIds[$row->student_id])) {
                $this->skipMissingFk('pocket_transactions');
                continue;
            }
            $this->importRow('pocket_transactions', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'student_id' => $row->student_id,
                'type' => $row->type,
                'amount' => $row->amount,
                'balance_after' => $row->balance_after,
                'performed_by' => $this->fkOrNull($row->performed_by, $this->userIds),
                'note' => $row->note,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importBudgets(): void
    {
        foreach (DB::connection('legacy')->table('budgets')->get() as $row) {
            if (! isset($this->userIds[$row->staff_id])) {
                $this->skipMissingFk('budgets');
                continue;
            }
            $this->importRow('budgets', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'staff_id' => $row->staff_id,
                'department_id' => $this->fkOrNull($row->department_id, $this->departmentIds),
                'month' => $row->month,
                'year' => $row->year,
                'status' => $row->status,
                'current_step' => $row->current_step,
                'total_amount' => $row->total_amount,
                'note' => $row->note,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], $this->budgetIds);
        }
    }

    private function importBudgetItems(): void
    {
        foreach (DB::connection('legacy')->table('budget_items')->get() as $row) {
            if (! isset($this->budgetIds[$row->budget_id])) {
                $this->skipMissingFk('budget_items');
                continue;
            }
            $this->importRow('budget_items', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'budget_id' => $row->budget_id,
                'item' => $row->item,
                'description' => $row->description,
                'price' => $row->price,
                'status' => $row->status,
                'note' => $row->note,
                'approved_by' => $this->fkOrNull($row->approved_by, $this->userIds),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], $this->budgetItemIds);
        }
    }

    private function importInvoices(): void
    {
        foreach (DB::connection('legacy')->table('invoices')->get() as $row) {
            if (! isset($this->budgetItemIds[$row->budget_item_id]) || ! isset($this->budgetIds[$row->budget_id])) {
                $this->skipMissingFk('invoices');
                continue;
            }
            $this->importRow('invoices', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'budget_item_id' => $row->budget_item_id,
                'budget_id' => $row->budget_id,
                'amount' => $row->amount,
                'status' => $row->status,
                'approved_by_do_id' => $this->fkOrNull($row->approved_by_do_id, $this->userIds),
                'paid_by_finance_id' => $this->fkOrNull($row->paid_by_finance_id, $this->userIds),
                'payment_date' => $row->payment_date,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importLoanCategories(): void
    {
        foreach (DB::connection('legacy')->table('loan_categories')->get() as $row) {
            if (! isset($this->userIds[$row->created_by_treasurer_id])) {
                $this->skipMissingFk('loan_categories');
                continue;
            }
            $this->importRow('loan_categories', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'name' => $row->name,
                'description' => $row->description,
                'min_amount' => $row->min_amount,
                'max_amount' => $row->max_amount,
                'max_installments' => $row->max_installments,
                'interest_rate' => $row->interest_rate,
                'eligibility_criteria' => $row->eligibility_criteria,
                'restrictions' => $row->restrictions,
                'created_by_treasurer_id' => $row->created_by_treasurer_id,
                'is_active' => $row->is_active,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], $this->loanCategoryIds);
        }
    }

    private function importLoans(): void
    {
        foreach (DB::connection('legacy')->table('loans')->get() as $row) {
            if (! isset($this->staffIds[$row->staff_id]) || ! isset($this->loanCategoryIds[$row->loan_category_id])) {
                $this->skipMissingFk('loans');
                continue;
            }
            $this->importRow('loans', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'staff_id' => $row->staff_id,
                'loan_category_id' => $row->loan_category_id,
                'amount_applied' => $row->amount_applied,
                'amount_approved' => $row->amount_approved,
                'interest_rate_applied' => $row->interest_rate_applied,
                'installments' => $row->installments,
                'salary_at_application' => $row->salary_at_application,
                'application_date' => $row->application_date,
                'approval_date' => $row->approval_date,
                'disbursement_date' => $row->disbursement_date,
                'expected_end_date' => $row->expected_end_date,
                'approval_level' => $row->approval_level,
                'chief_accountant_approved_by' => $this->fkOrNull($row->chief_accountant_approved_by, $this->userIds),
                'chief_accountant_approved_at' => $row->chief_accountant_approved_at,
                'accountant_approved_by' => $this->fkOrNull($row->accountant_approved_by, $this->userIds),
                'accountant_approved_at' => $row->accountant_approved_at,
                'treasurer_approved_by' => $this->fkOrNull($row->treasurer_approved_by, $this->userIds),
                'treasurer_approved_at' => $row->treasurer_approved_at,
                'rejection_reason' => $row->rejection_reason,
                'status' => $row->status,
                'treasurer_notes' => $row->treasurer_notes,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], $this->loanIds);
        }
    }

    private function importLoanRepayments(): void
    {
        foreach (DB::connection('legacy')->table('loan_repayments')->get() as $row) {
            if (! isset($this->loanIds[$row->loan_id])) {
                $this->skipMissingFk('loan_repayments');
                continue;
            }
            $this->importRow('loan_repayments', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'loan_id' => $row->loan_id,
                'installment_number' => $row->installment_number,
                'amount' => $row->amount,
                'due_date' => $row->due_date,
                'paid_date' => $row->paid_date,
                'status' => $row->status,
                'payment_reference' => $row->payment_reference,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    // -----------------------------------------------------------------
    // Counseling / guidance
    // -----------------------------------------------------------------

    private function importClassroomGuidances(): void
    {
        foreach (DB::connection('legacy')->table('classroom_guidances')->get() as $row) {
            if (! isset($this->classIds[$row->class_id]) || ! isset($this->staffIds[$row->created_by])) {
                $this->skipMissingFk('classroom_guidances');
                continue;
            }
            $this->importRow('classroom_guidances', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'class_id' => $row->class_id,
                'date' => $row->date,
                'tasks' => $row->tasks,
                'achievements' => $row->achievements,
                'challenges' => $row->challenges,
                'created_by' => $row->created_by,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importCounselingIntakeForms(): void
    {
        $passthroughColumns = [
            'gender', 'age', 'stream', 'education_program', 'g_performance', 'living_situation',
            'father_name', 'father_address', 'father_occupation', 'father_age', 'father_phone',
            'guardian_name', 'guardian_relationship', 'mother_name', 'mother_address', 'mother_occupation',
            'mother_age', 'mother_phone', 'parents_relationship', 'siblings_brothers', 'siblings_sisters',
            'birth_order', 'referred_by', 'health_problems', 'previous_counseling', 'reason_for_counseling',
            'chief_complaint', 'understanding_of_services', 'counseling_type',
        ];

        foreach (DB::connection('legacy')->table('counseling_intake_forms')->get() as $row) {
            if (! isset($this->studentIds[$row->student_id])) {
                $this->skipMissingFk('counseling_intake_forms');
                continue;
            }
            $data = [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'student_id' => $row->student_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
            foreach ($passthroughColumns as $col) {
                $data[$col] = $row->$col ?? null;
            }
            $this->importRow('counseling_intake_forms', $data);
        }
    }

    private function importIndividualSessionReports(): void
    {
        foreach (DB::connection('legacy')->table('individual_session_reports')->get() as $row) {
            if (! isset($this->studentIds[$row->student_id]) || ! isset($this->userIds[$row->user_id])) {
                $this->skipMissingFk('individual_session_reports');
                continue;
            }
            $this->importRow('individual_session_reports', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'student_id' => $row->student_id,
                'user_id' => $row->user_id,
                'date' => $row->date,
                'time' => $row->time,
                'session_number' => $row->session_number,
                'presenting_problem' => $row->presenting_problem,
                'work_done' => $row->work_done,
                'assessment_progress' => $row->assessment_progress,
                'intervention_plan' => $row->intervention_plan,
                'follow_up' => $row->follow_up,
                'biopsychosocial_formulation' => $row->biopsychosocial_formulation,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importGroupCounselingSessionReports(): void
    {
        // Note: this table has no school_id column in either schema.
        foreach (DB::connection('legacy')->table('group_counseling_session_reports')->get() as $row) {
            $this->importRow('group_counseling_session_reports', [
                'id' => $row->id,
                'group_name' => $row->group_name,
                'members' => $row->members,
                'date' => $row->date,
                'time' => $row->time,
                'session_number' => $row->session_number,
                'presenting_problem' => $row->presenting_problem,
                'work_done' => $row->work_done,
                'assessment_progress' => $row->assessment_progress,
                'intervention_plan' => $row->intervention_plan,
                'follow_up' => $row->follow_up,
                'biopsychosocial_formulation' => $row->biopsychosocial_formulation,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'user_id' => $this->fkOrNull($row->user_id, $this->userIds),
            ], $this->groupReportIds);
        }
    }

    private function importGroupCounselingStudent(): void
    {
        foreach (DB::connection('legacy')->table('group_counseling_student')->get() as $row) {
            if (! isset($this->groupReportIds[$row->group_counseling_session_report_id]) || ! isset($this->studentIds[$row->student_id])) {
                $this->skipMissingFk('group_counseling_student');
                continue;
            }
            $this->importRow('group_counseling_student', [
                'id' => $row->id,
                'report_id' => $row->group_counseling_session_report_id,
                'student_id' => $row->student_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function importInterestInventories(): void
    {
        foreach (DB::connection('legacy')->table('interest_inventories')->get() as $row) {
            if (! isset($this->studentIds[$row->student_id]) || ! isset($this->userIds[$row->created_by])) {
                $this->skipMissingFk('interest_inventories');
                continue;
            }
            $data = [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'student_id' => $row->student_id,
                'created_by' => $row->created_by,
                'date' => $row->date,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
            for ($i = 1; $i <= 17; $i++) {
                $data["q{$i}"] = $row->{"q{$i}"} ?? null;
            }
            $this->importRow('interest_inventories', $data);
        }
    }

    private function importAptitudeQuestions(): void
    {
        foreach (DB::connection('legacy')->table('aptitude_questions')->get() as $row) {
            $this->importRow('aptitude_questions', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'question_text' => $row->question_text,
                'type' => $row->type,
                'section' => $row->section,
                'image' => $row->image,
                'options' => $row->options,
                'correct_answer' => $row->correct_answer,
                'marks' => $row->marks,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], $this->aptitudeQuestionIds);
        }
    }

    private function importAptitudeAttempts(): void
    {
        foreach (DB::connection('legacy')->table('aptitude_attempts')->get() as $row) {
            if (! isset($this->studentIds[$row->student_id]) || ! isset($this->userIds[$row->counselor_id])) {
                $this->skipMissingFk('aptitude_attempts');
                continue;
            }
            $this->importRow('aptitude_attempts', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'student_id' => $row->student_id,
                'counselor_id' => $row->counselor_id,
                'total_score' => $row->total_score,
                'time_taken' => $row->time_taken,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], $this->aptitudeAttemptIds);
        }
    }

    private function importAptitudeAnswers(): void
    {
        foreach (DB::connection('legacy')->table('aptitude_answers')->get() as $row) {
            if (! isset($this->aptitudeAttemptIds[$row->attempt_id]) || ! isset($this->aptitudeQuestionIds[$row->question_id])) {
                $this->skipMissingFk('aptitude_answers');
                continue;
            }
            $this->importRow('aptitude_answers', [
                'id' => $row->id,
                'school_id' => $this->schoolId,
                'attempt_id' => $row->attempt_id,
                'question_id' => $row->question_id,
                'student_answer' => $row->student_answer,
                'obtained_marks' => $row->obtained_marks,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    // -----------------------------------------------------------------
    // RBAC (roles only — permissions/role_has_permissions are left as
    // freshly seeded by the new app, not migrated)
    // -----------------------------------------------------------------

    private function loadRoleMap(): void
    {
        foreach (DB::table('roles')->get() as $r) {
            $this->roleIdByName[$r->name] = $r->id;
        }
    }

    private function mapLegacyRoleName(string $oldRoleName): ?string
    {
        return match ($oldRoleName) {
            'Admin', 'Teacher', 'Staff', 'Finance', 'Dorm Master' => $oldRoleName,
            // No guardian RBAC role in the new schema — guardian portal access
            // is via guardians.user_id, not Spatie roles.
            'guardian' => null,
            // Director, Assistant Director, DO, Librarian, Coordinator,
            // Academic, HOD, Doctor, Counselor: no equivalent -> generic Staff.
            default => 'Staff',
        };
    }

    private function importModelHasRoles(): void
    {
        $oldRoleNames = DB::connection('legacy')->table('roles')->pluck('name', 'id');

        foreach (DB::connection('legacy')->table('model_has_roles')->where('model_type', 'App\\Models\\User')->get() as $row) {
            if (! isset($this->userIds[$row->model_id])) {
                $this->skipMissingFk('model_has_roles');
                continue;
            }

            $oldRoleName = $oldRoleNames[$row->role_id] ?? null;
            $newRoleName = $oldRoleName !== null ? $this->mapLegacyRoleName($oldRoleName) : null;

            if ($newRoleName === null || ! isset($this->roleIdByName[$newRoleName])) {
                continue;
            }

            $newRoleId = $this->roleIdByName[$newRoleName];

            $alreadyAssigned = DB::table('model_has_roles')->where([
                'role_id' => $newRoleId,
                'model_type' => 'App\\Models\\User',
                'model_id' => $row->model_id,
            ])->exists();

            if ($alreadyAssigned) {
                continue;
            }

            $this->importRow('model_has_roles', [
                'role_id' => $newRoleId,
                'model_type' => 'App\\Models\\User',
                'model_id' => $row->model_id,
            ]);
        }
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    private function fkOrNull(mixed $value, array $knownIds): ?int
    {
        if ($value === null) {
            return null;
        }

        return isset($knownIds[$value]) ? (int) $value : null;
    }

    private function importRow(string $table, array $data, ?array &$trackSet = null): void
    {
        $id = $data['id'] ?? null;

        try {
            DB::table($table)->insert($data);
            $this->stats[$table]['imported'] = ($this->stats[$table]['imported'] ?? 0) + 1;
            if ($trackSet !== null && $id !== null) {
                $trackSet[$id] = true;
            }
        } catch (Throwable $e) {
            $this->stats[$table]['skipped'] = ($this->stats[$table]['skipped'] ?? 0) + 1;
        }
    }

    private function skipMissingFk(string $table): void
    {
        $this->stats[$table]['skipped'] = ($this->stats[$table]['skipped'] ?? 0) + 1;
    }

    private function skipJunk(string $table): void
    {
        $this->stats[$table]['skipped_junk'] = ($this->stats[$table]['skipped_junk'] ?? 0) + 1;
    }

    private function looksLikeJunk(?string $first, ?string $last): bool
    {
        $words = array_filter([trim((string) $first), trim((string) $last)], fn ($w) => $w !== '');

        if (empty($words)) {
            return false;
        }

        $junkWords = ['test', 'teat', 'demo', 'sample', 'asdf', 'qwerty', 'xxx', 'tmp', 'temp'];

        foreach ($words as $w) {
            $lw = strtolower($w);
            if (in_array($lw, $junkWords, true)) {
                return true;
            }
            // a single character repeated up to 6 times, e.g. "a", "aaaa", "ssss", "qqqq"
            if (preg_match('/^(.)\1{0,5}$/', $lw)) {
                return true;
            }
        }

        return false;
    }

    private function confirmLegacyConnection(): bool
    {
        try {
            DB::connection('legacy')->getPdo();

            return true;
        } catch (Throwable $e) {
            $this->error('Could not connect to the "legacy" database: '.$e->getMessage());
            $this->error('Set LEGACY_DB_HOST / LEGACY_DB_DATABASE / LEGACY_DB_USERNAME / LEGACY_DB_PASSWORD in .env first.');

            return false;
        }
    }

    private function runAudit(): void
    {
        foreach (['users', 'staff', 'guardians'] as $table) {
            $this->info("--- Legacy {$table} ---");
            $rows = DB::connection('legacy')->table($table)->orderBy('id')->get();
            $this->table(
                ['id', 'name', 'email', 'looks_junk'],
                $rows->map(fn ($r) => [
                    $r->id,
                    trim($r->first_name.' '.$r->last_name),
                    $r->email,
                    $this->looksLikeJunk($r->first_name, $r->last_name) ? 'YES' : '',
                ])->all()
            );
        }

        $this->comment('Add any extra ids to $excludeUserIds / $excludeStaffIds / $excludeGuardianIds at the top of ImportLegacySchoolData.php, then run: php artisan legacy:import --dry-run');
    }

    private function printReport(): void
    {
        $rows = [];
        foreach ($this->stats as $table => $counts) {
            $rows[] = [
                $table,
                $counts['imported'] ?? 0,
                $counts['skipped'] ?? 0,
                $counts['skipped_junk'] ?? 0,
            ];
        }
        $this->table(['table', 'imported', 'skipped (missing fk / conflict)', 'skipped (junk)'], $rows);
    }
}
