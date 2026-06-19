<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── PROGRAMS (ICS, Medical, Engineering, FAIT) ──────────────
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();   // ICS, MED, ENG, FAIT
            $table->string('name');                  // Intermediate Computer Science
            $table->enum('campus_scope', ['boys', 'girls', 'both'])->default('both');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // ── SECTIONS (PCB1, PCG2, SCB3, etc.) ───────────────────────
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('code', 15)->unique();    // PCB1, PCG2, SEB/SMB
            $table->foreignId('program_id')->constrained('programs');
            $table->enum('campus', ['boys', 'girls'])->index();
            $table->enum('year', ['first', 'second'])->index();
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('is_combined')->default(false); // e.g. PEB/PMB combined section
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['campus', 'year', 'program_id']);
        });

        // ── SUBJECTS (master list of all subjects) ──────────────────
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');                  // Physics, Computer, Islamiyat, TTQ...
            $table->string('short_code', 10)->nullable(); // PHY, CS, ISL, TTQ
            $table->timestamps();
        });

        // ── PROGRAM_SUBJECT (which subjects belong to a program+year) ─
        Schema::create('program_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->enum('year', ['first', 'second']);
            $table->unsignedInteger('default_marks')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            // Used for the rotating 6th subject (Islamiyat/TTQ, SST/PST)
            $table->boolean('is_rotating')->default(false);
            $table->string('rotation_group')->nullable(); // e.g. "religious_1st_yr"
            $table->timestamps();

            $table->unique(['program_id', 'subject_id', 'year'], 'program_subject_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_subject');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('programs');
    }
};
