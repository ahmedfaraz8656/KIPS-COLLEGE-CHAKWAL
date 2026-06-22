<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_events', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('campus_scope', ['boys', 'girls', 'both'])->default('both');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_events');
    }
};
