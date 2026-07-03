<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('action')->unique();
            $table->integer('points');
            $table->timestamps();

            $table->unique(['tenant_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_configurations');
    }
};
