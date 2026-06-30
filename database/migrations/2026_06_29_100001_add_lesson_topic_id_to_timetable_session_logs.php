<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_session_logs', function (Blueprint $table) {
            $table->foreignId('lesson_topic_id')->nullable()->after('notes')
                  ->constrained('lesson_topics')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('timetable_session_logs', function (Blueprint $table) {
            $table->dropForeign(['lesson_topic_id']);
            $table->dropColumn('lesson_topic_id');
        });
    }
};
