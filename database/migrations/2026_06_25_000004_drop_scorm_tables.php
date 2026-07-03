<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('scorm_trackings');
        Schema::dropIfExists('scorm_scoes');
        Schema::dropIfExists('scorm_packages');
    }

    public function down(): void
    {
        // SCORM tables removed permanently
    }
};
