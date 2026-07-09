<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Widens a few columns whose old (single-tenant) values don't fit the
 * current enum/type definitions, so the legacy import command can insert
 * historical data without losing information. See app/Console/Commands/
 * ImportLegacySchoolData.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `budgets` MODIFY `status` ENUM('pending','partially_approved','approved','declined','in_use','completed') NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE `budget_items` MODIFY `status` ENUM('pending','approved','declined','rejected','withdrawn','used') NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE `enrollments` MODIFY `roll_no` VARCHAR(20) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `budgets` MODIFY `status` ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE `budget_items` MODIFY `status` ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE `enrollments` MODIFY `roll_no` INT NULL");
    }
};
