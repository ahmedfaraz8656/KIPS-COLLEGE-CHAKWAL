<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Admission, Monthly Tuition, Examination, Miscellaneous
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
        });

        // Rate configuration per Program + Campus (+ optional Year)
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_category_id')->constrained('fee_categories')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->cascadeOnDelete();
            $table->enum('campus', ['boys', 'girls', 'both'])->default('both');
            $table->enum('year', ['first', 'second', 'both'])->default('both');
            $table->decimal('amount', 10, 2);
            $table->enum('installment_plan', ['full', '2', '3', '4', 'custom'])->default('full');
            $table->timestamps();
        });

        // Per-student payment ledger
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('fee_category_id')->constrained('fee_categories');
            $table->date('payment_date');
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('waiver_amount', 10, 2)->default(0);
            $table->string('waiver_reason')->nullable();
            $table->enum('payment_mode', ['cash', 'bank', 'jazzcash', 'easypaisa'])->default('cash');
            $table->string('receipt_number')->unique();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->boolean('is_demo')->default(false);
            $table->timestamps();

            $table->index(['student_id', 'fee_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('fee_categories');
    }
};
