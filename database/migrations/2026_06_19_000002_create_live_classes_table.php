<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_classes', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('provider')->default('jitsi');
            $table->string('meeting_url')->nullable();
            $table->string('meeting_id')->nullable();
            $table->string('password')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('recording_url')->nullable();
            $table->boolean('recurring')->default(false);
            $table->string('status')->default('scheduled');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_classes');
    }
};
