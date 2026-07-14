<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * These three flags were always in the Exam model's fillable/casts, in
     * the create/edit forms, and even in WHERE clauses (StudentResultController,
     * MarkController) — but the columns never existed. Saving an exam silently
     * discarded the checkboxes, and the queries that filter on them threw
     * SQL errors.
     */
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (! Schema::hasColumn('exams', 'include_in_term_final')) {
                $table->boolean('include_in_term_final')->default(false)->after('academic_session_id');
            }
            if (! Schema::hasColumn('exams', 'include_in_year_final')) {
                $table->boolean('include_in_year_final')->default(false)->after('include_in_term_final');
            }
            if (! Schema::hasColumn('exams', 'is_terminal_exam')) {
                $table->boolean('is_terminal_exam')->default(false)->after('include_in_year_final');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['include_in_term_final', 'include_in_year_final', 'is_terminal_exam']);
        });
    }
};
