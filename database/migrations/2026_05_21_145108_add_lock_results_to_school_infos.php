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
        Schema::table('school_infos', function (Blueprint $table) {
    $table->boolean('lock_results_for_guardians')->default(true);  // true = lock enabled
    $table->boolean('lock_results_only_overdue')->default(false);  // false = lock if any outstanding; true = lock only if overdue bills
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_infos', function (Blueprint $table) {
            //
        });
    }
};
