<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // System-generated, globally unique roll number (e.g. B1C0001)
            $table->string('roll_number', 20)->unique();
            $table->boolean('roll_number_manually_edited')->default(false);

            // Personal Info
            $table->string('name');
            $table->string('father_name');
            $table->string('cnic_bform', 20)->nullable();
            $table->date('dob')->nullable();
            $table->string('whatsapp', 20);
            $table->string('alternate_phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('photo')->nullable();

            // Enrollment
            $table->enum('campus', ['boys', 'girls'])->index();
            $table->enum('year', ['first', 'second'])->index();
            $table->foreignId('program_id')->constrained('programs');
            $table->foreignId('section_id')->constrained('sections');
            $table->date('enrollment_date');

            // 9th Class Record
            $table->string('ninth_board')->nullable();
            $table->string('ninth_roll_no')->nullable();
            $table->year('ninth_year')->nullable();
            $table->unsignedInteger('ninth_total_marks')->nullable();
            $table->unsignedInteger('ninth_obtained_marks')->nullable();
            $table->string('ninth_stream')->nullable(); // Science/Arts/Computer

            // 10th Class Record
            $table->string('tenth_board')->nullable();
            $table->string('tenth_roll_no')->nullable();
            $table->year('tenth_year')->nullable();
            $table->unsignedInteger('tenth_total_marks')->nullable();
            $table->unsignedInteger('tenth_obtained_marks')->nullable();
            $table->string('tenth_stream')->nullable();

            // Status tracking
            $table->enum('status', ['active', 'transferred', 'promoted', 'graduated', 'left'])
                  ->default('active');
            $table->text('status_note')->nullable(); // "Transferred from PCB1 to PCB2 on ..."

            $table->boolean('is_demo')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['campus', 'year', 'section_id']);
        });

        // History log of every section move/promotion for a student
        Schema::create('student_section_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('from_section_id')->nullable()->constrained('sections');
            $table->foreignId('to_section_id')->constrained('sections');
            $table->enum('action', ['move', 'promote', 'initial_admission']);
            $table->text('reason')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_section_history');
        Schema::dropIfExists('students');
    }
};
