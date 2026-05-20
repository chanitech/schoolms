<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained();
            $table->foreignId('loan_category_id')->constrained();
            
            // Loan details
            $table->decimal('amount_applied', 10, 2);
            $table->decimal('amount_approved', 10, 2)->nullable();
            $table->decimal('interest_rate_applied', 5, 2);
            $table->integer('installments');
            $table->decimal('salary_at_application', 10, 2);
            
            // Dates
            $table->date('application_date')->default(now());
            $table->date('approval_date')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->date('expected_end_date')->nullable(); // final due date
            
            // Approval chain (3 levels)
            $table->tinyInteger('approval_level')->default(0); // 0=none,1=Chief Accountant,2=Accountant,3=Treasurer
            $table->foreignId('chief_accountant_approved_by')->nullable()->constrained('users');
            $table->timestamp('chief_accountant_approved_at')->nullable();
            $table->foreignId('accountant_approved_by')->nullable()->constrained('users');
            $table->timestamp('accountant_approved_at')->nullable();
            $table->foreignId('treasurer_approved_by')->nullable()->constrained('users');
            $table->timestamp('treasurer_approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Status: pending, approved, rejected, active, closed
            $table->enum('status', ['pending', 'approved', 'rejected', 'active', 'closed'])->default('pending');
            $table->text('treasurer_notes')->nullable();
            
            $table->timestamps();
            
            $table->index('status');
            $table->index('approval_level');
            $table->index('staff_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('loans');
    }
};