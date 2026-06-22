<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('period_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('period_number');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->enum('day', ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT']);
            $table->foreignId('period_slot_id')->constrained('period_slots')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['section_id', 'day', 'period_slot_id'], 'timetable_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
        Schema::dropIfExists('period_slots');
    }
};
