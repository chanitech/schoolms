<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Attendance status is now marked by the class coordinator, not
     * self-reported. A subject teacher may log topic coverage before the
     * coordinator has marked the session, so the status must be nullable
     * ("not yet marked").
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE timetable_session_logs MODIFY status ENUM('attended', 'late', 'absent', 'other') NULL DEFAULT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE timetable_session_logs SET status = 'other' WHERE status IS NULL");
            DB::statement("ALTER TABLE timetable_session_logs MODIFY status ENUM('attended', 'late', 'absent', 'other') NOT NULL");
        }
    }
};
