<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Test 1, FLP 1, Pre-Board, Send Up
            $table->string('type')->index();      // test, flp, send_up, pre_board, custom
            $table->unsignedTinyInteger('sequence')->nullable(); // 1-10 for Test series
            $table->date('exam_date');
            $table->enum('campus_scope', ['boys', 'girls', 'both']);
            $table->text('description')->nullable();
            $table->foreignId('grading_template_id')->nullable()->constrained('grading_templates');

            // Due-date / lock mechanism
            $table->dateTime('marks_due_date')->nullable();
            $table->dateTime('marks_due_date_extended_to')->nullable();
            $table->boolean('is_locked')->default(false);

            $table->boolean('is_demo')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Which sections this exam applies to
        Schema::create('exam_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['exam_id', 'section_id']);
        });

        // Configured max marks per subject, per exam, per program+year
        // (applies to all sections of that program/year/campus combination)
        Schema::create('exam_subject_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->enum('year', ['first', 'second']);
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unsignedInteger('total_marks');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['exam_id', 'program_id', 'year', 'subject_id'], 'exam_subject_marks_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_subject_marks');
        Schema::dropIfExists('exam_sections');
        Schema::dropIfExists('exams');
    }
};
