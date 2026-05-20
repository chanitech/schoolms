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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
$table->foreignId('budget_item_id')->constrained()->cascadeOnDelete();
$table->foreignId('budget_id')->constrained()->cascadeOnDelete();
$table->decimal('amount', 12, 2);
$table->enum('status', ['pending', 'approved_by_do', 'rejected_by_do', 'paid'])->default('pending');
$table->foreignId('approved_by_do_id')->nullable()->constrained('users');
$table->foreignId('paid_by_finance_id')->nullable()->constrained('users');
$table->timestamp('payment_date')->nullable();
$table->timestamps();

        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
