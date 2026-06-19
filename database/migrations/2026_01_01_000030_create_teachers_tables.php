<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('cnic', 20)->nullable();
            $table->string('whatsapp', 20);
            $table->string('alternate_phone', 20)->nullable();
            $table->string('email')->unique();
            $table->date('date_of_joining')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->string('qualification')->nullable();
            $table->string('photo')->nullable();
            $table->enum('campus_access', ['boys', 'girls', 'both'])->default('both');
            $table->boolean('status')->default(true);
            $table->boolean('is_demo')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        // Which subject a teacher teaches in which section
        Schema::create('teacher_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'section_id', 'subject_id'], 'teacher_section_subject_unique');
        });

        // Class Incharge assignment (one section -> one primary incharge + optional substitute)
        Schema::create('section_incharges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('substitute_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamps();

            $table->unique('section_id'); // one incharge record per section
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_incharges');
        Schema::dropIfExists('teacher_sections');
        Schema::dropIfExists('teachers');
    }
};
