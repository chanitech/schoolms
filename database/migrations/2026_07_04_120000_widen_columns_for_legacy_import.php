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
        // Raw `MODIFY` is MySQL-only syntax. This migration exists solely to
        // prep the production MySQL database for the (also MySQL-only)
        // legacy import command, so it's a no-op on other drivers rather
        // than failing migrate:fresh in the sqlite-backed test suite (same
        // guard convention used elsewhere in this file set, e.g.
        // update_session_logs_status_enum).
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE `budgets` MODIFY `status` ENUM('pending','partially_approved','approved','declined','in_use','completed') NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE `budget_items` MODIFY `status` ENUM('pending','approved','declined','rejected','withdrawn','used') NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE `enrollments` MODIFY `roll_no` VARCHAR(20) NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE `budgets` MODIFY `status` ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE `budget_items` MODIFY `status` ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE `enrollments` MODIFY `roll_no` INT NULL");
    }
};
