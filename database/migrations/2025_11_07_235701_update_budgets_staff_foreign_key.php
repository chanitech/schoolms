<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            // Drop the old foreign key
            $table->dropForeign(['staff_id']);

            // Re-add foreign key pointing to users table
            $table->foreign('staff_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            // Drop foreign key pointing to users
            $table->dropForeign(['staff_id']);

            // Optionally recreate the old foreign key (if staff table exists)
            $table->foreign('staff_id')
                  ->references('id')
                  ->on('staff')
                  ->onDelete('cascade');
        });
    }
};
