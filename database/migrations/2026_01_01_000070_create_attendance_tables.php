<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->enum('type', ['public', 'college']);
            $table->enum('campus_scope', ['boys', 'girls', 'both'])->default('both');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['date', 'campus_scope']);
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->date('date')->index();
            $table->enum('status', ['present', 'absent', 'leave', 'holiday'])->index();
            $table->boolean('is_late')->default(false);
            $table->time('marked_at_time')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'date'], 'student_attendance_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('holidays');
    }
};
