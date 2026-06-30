<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('lendings') || Schema::hasColumn('lendings', 'quantity')) return;

        Schema::table('lendings', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('book_id');
        });
    }

    public function down(): void
    {
        Schema::table('lendings', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
