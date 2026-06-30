<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->enum('status', ['draft', 'reviewed', 'published'])->default('draft')->after('is_annual_exam');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete()->after('reviewed_at');
            $table->timestamp('published_at')->nullable()->after('published_by');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['published_by']);
            $table->dropColumn(['status', 'reviewed_by', 'reviewed_at', 'published_by', 'published_at']);
        });
    }
};
