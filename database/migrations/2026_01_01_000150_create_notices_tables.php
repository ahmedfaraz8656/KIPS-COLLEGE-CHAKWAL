<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('target', ['all', 'teachers', 'students', 'parents', 'campus'])->default('all');
            $table->enum('campus_scope', ['boys', 'girls', 'both'])->default('both');
            $table->enum('priority', ['normal', 'important', 'urgent'])->default('normal');
            $table->string('attachment')->nullable();
            $table->dateTime('post_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('notice_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->unique(['notice_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_reads');
        Schema::dropIfExists('notices');
    }
};
