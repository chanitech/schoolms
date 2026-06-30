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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();            // subdomain key: kitungwa, memaasep
            $table->string('logo')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('motto')->nullable();
            $table->string('website')->nullable();
            $table->enum('subscription_status', ['active', 'trial', 'expired', 'cancelled'])->default('active');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->string('plan')->default('pro');      // basic | pro
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
