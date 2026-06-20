<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        // Parent accounts can be linked to one or more children (a parent
        // may have multiple kids enrolled — each link row is one child).
        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // the Parent's login account
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_student');
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
