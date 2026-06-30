<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete()->after('category');
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->nullOnDelete()->after('academic_session_id');
            $table->string('subject')->nullable()->after('class_id');
            $table->string('language')->default('English')->after('subject');
            $table->date('document_date')->nullable()->after('language');
            $table->string('author')->nullable()->after('document_date');
            $table->json('tags')->nullable()->after('author');
            $table->unsignedInteger('download_count')->default(0)->after('tags');
            $table->boolean('is_featured')->default(false)->after('download_count');
            $table->boolean('is_restricted')->default(false)->after('is_featured'); // staff-only
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['academic_session_id']);
            $table->dropForeign(['class_id']);
            $table->dropColumns([
                'academic_session_id', 'class_id', 'subject', 'language',
                'document_date', 'author', 'tags', 'download_count',
                'is_featured', 'is_restricted',
            ]);
        });
    }
};
