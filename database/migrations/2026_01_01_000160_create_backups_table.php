<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->enum('type', ['auto', 'manual', 'snapshot'])->default('manual');
            $table->string('label')->nullable(); // e.g. "Pre-delete snapshot: Bulk Delete Students by Ahmed"
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamp('expires_at')->nullable(); // snapshots auto-expire after 7 days
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
