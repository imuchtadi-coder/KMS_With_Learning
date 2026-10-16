<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // starter, profesional, enterprise
            $table->string('name'); // Starter, Profesional, Enterprise
            $table->decimal('price', 12, 2)->default(0);
            $table->string('billing_period')->default('bulan'); // bulan, tahun
            $table->text('description')->nullable();
            $table->string('badge')->nullable(); // Populer, Best Value, dll
            $table->json('features')->nullable(); // list array of strings
            $table->integer('max_users')->nullable(); // null = unlimited
            $table->integer('max_courses')->nullable(); // null = unlimited
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
