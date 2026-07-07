<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MarkController::store()/update() and both mark-import classes have
     * always written 'class_id' onto Mark records (it's in the model's
     * $fillable), but no migration ever added the column — it must have
     * been added directly on production outside of Laravel's migrations at
     * some point. This brings any environment missing it (e.g. a fresh
     * local dev DB) in line with what the app has always assumed exists.
     */
    public function up(): void
    {
        if (Schema::hasColumn('marks', 'class_id')) {
            return;
        }

        Schema::table('marks', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->after('subject_id')
                ->constrained('school_classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('marks', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn('class_id');
        });
    }
};
