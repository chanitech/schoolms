<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Super Admin has no staff profile inside the schools it works in.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', fn (Blueprint $table) => $table->unsignedBigInteger('created_by')->nullable()->change());
    }

    public function down(): void
    {
    }
};
