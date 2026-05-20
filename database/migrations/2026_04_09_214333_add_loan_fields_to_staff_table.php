<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->decimal('basic_salary', 10, 2)->nullable()->after('position');
            $table->date('hire_date')->nullable()->after('basic_salary');
        });
    }

    public function down()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['basic_salary', 'hire_date']);
        });
    }
};