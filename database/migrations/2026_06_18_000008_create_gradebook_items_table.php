<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gradebook_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['quiz', 'assignment', 'exam']);
            $table->unsignedBigInteger('item_id');
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->integer('max_score')->default(100);
            $table->timestamps();

            $table->index(['course_id', 'type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gradebook_items');
    }
};
