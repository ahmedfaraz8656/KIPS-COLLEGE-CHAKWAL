<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Ahmed's clarified flow: when creating an exam, Admin picks
            // Year FIRST, then Campus, then Program tabs — so an exam
            // targets a specific year scope (first/second/both), just like
            // campus_scope targets boys/girls/both.
            $table->enum('year_scope', ['first', 'second', 'both'])->default('both')->after('campus_scope');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('year_scope');
        });
    }
};
