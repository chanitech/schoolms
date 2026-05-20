<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_bills', function (Blueprint $table) {
            // Make bill_id nullable
            $table->unsignedBigInteger('bill_id')->nullable()->change();
            
            // Add notes column for custom description
            $table->text('notes')->nullable()->after('balance');
        });
    }

    public function down()
    {
        Schema::table('student_bills', function (Blueprint $table) {
            $table->unsignedBigInteger('bill_id')->nullable(false)->change();
            $table->dropColumn('notes');
        });
    }
};