<?php

declare(strict_types=1);

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
        Schema::create('tenant_brandings', function (Blueprint $table) {
            $table->id();

            $table->string('tenant_id');

            $table->string('agency_name')->nullable();
            $table->string('tagline')->nullable();

            $table->string('logo_path')->nullable();
            $table->string('certificate_logo_path')->nullable();

            $table->timestamps();

            $table->unique('tenant_id');

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_brandings');
    }
};