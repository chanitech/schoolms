<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('plate_number');
            $table->string('name')->nullable();
            $table->unsignedInteger('capacity')->default(0);
            $table->foreignId('driver_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'plate_number']);
        });

        Schema::create('bus_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('bus_id')->nullable()->constrained('buses')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            // Independent from the class-scoped `bills` table (NOT NULL
            // class_id there) since one route commonly serves students
            // across many classes.
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bus_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('route_id')->constrained('bus_routes')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('stop_order')->default(0);
            $table->time('pickup_time')->nullable();
            $table->time('dropoff_time')->nullable();
            $table->timestamps();
        });

        Schema::create('student_transport_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            // One current assignment per student — history isn't tracked in
            // v1, matching the simplicity of dormitory_bed_allocations'
            // status field rather than a full log.
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->foreignId('route_id')->constrained('bus_routes')->cascadeOnDelete();
            $table->foreignId('stop_id')->nullable()->constrained('bus_stops')->nullOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('transport_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('route_id')->constrained('bus_routes')->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2);
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'month', 'year']);
        });

        Schema::create('transport_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('transport_fee_id')->constrained('transport_fees')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('cash');
            $table->string('reference')->nullable();
            $table->date('payment_date');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_payments');
        Schema::dropIfExists('transport_fees');
        Schema::dropIfExists('student_transport_assignments');
        Schema::dropIfExists('bus_stops');
        Schema::dropIfExists('bus_routes');
        Schema::dropIfExists('buses');
    }
};
