<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();

            $table->unsignedInteger('total_marks');     // copied from exam_subject_marks at entry time
            $table->unsignedInteger('obtained_marks')->default(0);

            // If student was absent/leave on exam date -> forced to 0, locked
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_leave')->default(false);

            $table->foreignId('entered_by')->nullable()->constrained('users');
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'exam_id', 'subject_id'], 'student_exam_subject_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_marks');
    }
};
