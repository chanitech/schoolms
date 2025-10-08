<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('name');
            $table->string('last_name')->after('first_name');
            $table->string('phone')->nullable()->after('email');
            $table->foreignId('department_id')->nullable()->constrained()->after('phone');
            $table->string('position')->nullable()->after('department_id');
            $table->string('photo')->nullable()->after('position');
            $table->string('role')->default('Staff')->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name','last_name','phone','position','photo','role']);
            $table->dropForeign(['department_id']);
        });
    }
};
