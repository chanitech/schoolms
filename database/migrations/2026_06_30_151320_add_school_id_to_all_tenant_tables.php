<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Every table that holds school-specific data.
    // Nullable to allow backfill; the BelongsToSchool trait enforces it at the app level.
    private array $tables = [
        // Core people
        'users', 'staff', 'students', 'guardians',

        // Academic structure
        'academic_sessions', 'school_classes', 'departments',
        'subjects', 'divisions', 'grades', 'enrollments',

        // Exams & results
        'exams', 'marks', 'student_results',
        'exam_questions', 'mark_question_scores',

        // HR
        'attendances', 'leaves', 'events', 'job_cards',
        'staff_salary_history', 'bank_statements',

        // Finance
        'bills', 'student_bills', 'payments',
        'pocket_transactions', 'invoices',
        'budgets', 'budget_items',
        'loans', 'loan_categories', 'loan_repayments',

        // Library
        'books', 'categories', 'lendings',

        // Dormitory
        'dormitories', 'dormitory_rooms', 'dormitory_beds', 'dormitory_bed_allocations',

        // Timetable
        'timetables', 'timetable_entries', 'timetable_periods',
        'timetable_reviews', 'timetable_session_logs',

        // Teaching
        'lesson_plans', 'lesson_topics', 'lesson_subtopics',
        'daily_reports', 'daily_report_activities',

        // AI
        'ai_conversations', 'ai_messages',

        // Inventory
        'inventory_categories', 'inventory_items', 'inventory_transactions',

        // Counseling & wellbeing
        'aptitude_questions', 'aptitude_attempts', 'aptitude_answers',
        'counseling_intake_forms', 'counseling_session_reports',
        'classroom_guidances', 'group_counseling_session_reports',
        'individual_session_reports', 'interest_inventories',

        // Settings & documents
        'school_infos', 'documents',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) continue;
            if (Schema::hasColumn($table, 'school_id')) continue;

            Schema::table($table, function (Blueprint $t) {
                // After 'id' so it's the second column
                $t->unsignedBigInteger('school_id')->nullable()->after('id');
                $t->index('school_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) continue;
            if (!Schema::hasColumn($table, 'school_id')) continue;

            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropIndex([$table . '_school_id_index']);
                $t->dropColumn('school_id');
            });
        }
    }
};
