<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->enum('type', ['purchase', 'issue', 'return', 'adjustment', 'damage', 'disposal']);
            $table->integer('quantity');
            $table->integer('balance_after');
            $table->string('reference_no')->nullable();
            $table->string('issued_to')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->date('transaction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
