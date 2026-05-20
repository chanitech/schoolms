<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable(); // KB
            $table->date('statement_month'); // first day of month, e.g. 2026-03-01
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            
            $table->unique(['staff_id', 'statement_month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bank_statements');
    }
};