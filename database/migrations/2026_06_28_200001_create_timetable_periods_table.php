<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);          // "Period 1", "Break", "Assembly"
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_break')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('order_no')->default(0);
            $table->timestamps();
        });

        // Seed default Tanzania secondary school timetable periods
        $defaults = [
            ['name' => 'Period 1',   'start_time' => '07:30', 'end_time' => '08:10', 'is_break' => false, 'order_no' => 1],
            ['name' => 'Period 2',   'start_time' => '08:10', 'end_time' => '08:50', 'is_break' => false, 'order_no' => 2],
            ['name' => 'Period 3',   'start_time' => '08:50', 'end_time' => '09:30', 'is_break' => false, 'order_no' => 3],
            ['name' => 'Period 4',   'start_time' => '09:30', 'end_time' => '10:10', 'is_break' => false, 'order_no' => 4],
            ['name' => 'Break',      'start_time' => '10:10', 'end_time' => '10:30', 'is_break' => true,  'order_no' => 5],
            ['name' => 'Period 5',   'start_time' => '10:30', 'end_time' => '11:10', 'is_break' => false, 'order_no' => 6],
            ['name' => 'Period 6',   'start_time' => '11:10', 'end_time' => '11:50', 'is_break' => false, 'order_no' => 7],
            ['name' => 'Period 7',   'start_time' => '11:50', 'end_time' => '12:30', 'is_break' => false, 'order_no' => 8],
            ['name' => 'Lunch',      'start_time' => '12:30', 'end_time' => '13:30', 'is_break' => true,  'order_no' => 9],
            ['name' => 'Period 8',   'start_time' => '13:30', 'end_time' => '14:10', 'is_break' => false, 'order_no' => 10],
            ['name' => 'Period 9',   'start_time' => '14:10', 'end_time' => '14:50', 'is_break' => false, 'order_no' => 11],
        ];

        foreach ($defaults as $d) {
            DB::table('timetable_periods')->insert(array_merge($d, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_periods');
    }
};
