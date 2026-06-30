<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantBackfillSeeder extends Seeder
{
    // All tables that received school_id in the migration
    private array $tables = [
        'users', 'staff', 'students', 'guardians',
        'academic_sessions', 'school_classes', 'departments',
        'subjects', 'divisions', 'grades', 'enrollments',
        'exams', 'marks', 'student_results',
        'exam_questions', 'mark_question_scores',
        'attendances', 'leaves', 'events', 'job_cards',
        'staff_salary_history', 'bank_statements',
        'bills', 'student_bills', 'payments',
        'pocket_transactions', 'invoices',
        'budgets', 'budget_items',
        'loans', 'loan_categories', 'loan_repayments',
        'books', 'categories', 'lendings',
        'dormitories', 'dormitory_rooms', 'dormitory_beds', 'dormitory_bed_allocations',
        'timetables', 'timetable_entries', 'timetable_periods',
        'timetable_reviews', 'timetable_session_logs',
        'lesson_plans', 'lesson_topics', 'lesson_subtopics',
        'daily_reports', 'daily_report_activities',
        'ai_conversations', 'ai_messages',
        'inventory_categories', 'inventory_items', 'inventory_transactions',
        'aptitude_questions', 'aptitude_attempts', 'aptitude_answers',
        'counseling_intake_forms', 'counseling_session_reports',
        'classroom_guidances', 'group_counseling_session_reports',
        'individual_session_reports', 'interest_inventories',
        'school_infos', 'documents',
    ];

    public function run(): void
    {
        // ── Step 1: Create the first tenant school from existing school_infos ──
        $info = DB::table('school_infos')->first();

        $schoolId = DB::table('schools')->insertGetId([
            'name'                  => $info?->name  ?? 'MEMA ASEP Learning Centre',
            'slug'                  => 'memaasep',
            'logo'                  => $info?->logo  ?? null,
            'email'                 => $info?->email ?? null,
            'phone'                 => $info?->phone ?? null,
            'address'               => $info?->address ?? null,
            'motto'                 => $info?->motto ?? null,
            'website'               => $info?->website ?? null,
            'subscription_status'   => 'active',
            'subscription_expires_at' => null,
            'plan'                  => 'pro',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        $this->command->info("Created school: ID={$schoolId}, slug=memaasep");

        // ── Step 2: Stamp every existing row with school_id ────────────────
        foreach ($this->tables as $table) {
            if (!$this->tableExists($table)) {
                $this->command->warn("  SKIP (no table): $table");
                continue;
            }
            if (!$this->columnExists($table, 'school_id')) {
                $this->command->warn("  SKIP (no column): $table");
                continue;
            }

            $affected = DB::table($table)
                ->whereNull('school_id')
                ->update(['school_id' => $schoolId]);

            $this->command->line("  <info>✔</info> $table → {$affected} rows");
        }

        $this->command->info("\nBackfill complete. All existing data belongs to school #{$schoolId}.");
        $this->command->info("Set TENANT_SCHOOL_ID={$schoolId} in your .env (already configured).");
    }

    private function tableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }

    private function columnExists(string $table, string $column): bool
    {
        return DB::getSchemaBuilder()->hasColumn($table, $column);
    }
}
