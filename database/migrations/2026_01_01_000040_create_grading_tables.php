<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Standard Grading", "Pre-Board Grading"
            $table->unsignedTinyInteger('min_pass_percent')->default(33);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('grading_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_template_id')->constrained('grading_templates')->cascadeOnDelete();
            $table->decimal('from_percent', 5, 2);
            $table->decimal('to_percent', 5, 2);
            $table->string('grade', 5);     // A+, A, B, C, D, E, F
            $table->string('remarks');      // Outstanding, Excellent, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_rules');
        Schema::dropIfExists('grading_templates');
    }
};
