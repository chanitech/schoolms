<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('marks', function (Blueprint $table) {
        $table->foreignId('academic_session_id')->after('exam_id')->constrained()->cascadeOnDelete();
    });
}

public function down()
{
    Schema::table('marks', function (Blueprint $table) {
        $table->dropForeign(['academic_session_id']);
        $table->dropColumn('academic_session_id');
    });
}

};
