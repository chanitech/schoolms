<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_subtopics', function (Blueprint $table) {
            $table->longText('lesson_plan_content')->nullable()->after('covered_by');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_subtopics', function (Blueprint $table) {
            $table->dropColumn('lesson_plan_content');
        });
    }
};
