<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // These tables got a global unique() before school_id existed. App-level
    // validation was later scoped per school (Rule::unique(...)->where('school_id', ...))
    // but the DB constraint itself was never widened, so two schools still can't
    // both use e.g. admission_no "1", grade "A", or session "2025/2026".
    private array $columns = [
        'students'          => ['admission_no'],
        'subjects'          => ['code'],
        'grades'            => ['name'],
        'divisions'         => ['name'],
        'departments'       => ['name'],
        'academic_sessions' => ['name'],
        'categories'        => ['name'],
        'inventory_items'   => ['code'],
        'guardians'         => ['phone', 'email', 'national_id'],
    ];

    public function up(): void
    {
        foreach ($this->columns as $table => $cols) {
            foreach ($cols as $col) {
                Schema::table($table, function (Blueprint $t) use ($table, $col) {
                    $t->dropUnique("{$table}_{$col}_unique");
                    $t->unique(['school_id', $col], "{$table}_school_id_{$col}_unique");
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $table => $cols) {
            foreach ($cols as $col) {
                Schema::table($table, function (Blueprint $t) use ($table, $col) {
                    $t->dropUnique("{$table}_school_id_{$col}_unique");
                    $t->unique($col, "{$table}_{$col}_unique");
                });
            }
        }
    }
};
