<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['result_published', 'low_attendance', 'fee_overdue', 'notice', 'exam_scheduled', 'general'])
                  ->default('general');
            $table->enum('target_type', ['all', 'role', 'campus', 'section', 'student'])->default('all');
            $table->string('target_value')->nullable(); // role name, campus, section_id, or student_id
            $table->enum('channel', ['in_app', 'whatsapp', 'both'])->default('in_app');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Per-user delivery + read tracking
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('app_notifications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->enum('whatsapp_status', ['pending', 'sent', 'delivered', 'failed', 'not_applicable'])->default('not_applicable');
            $table->timestamps();

            $table->unique(['notification_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_recipients');
        Schema::dropIfExists('app_notifications');
    }
};
