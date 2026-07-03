<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('is_exam')->default(false)->after('published');
            $table->dateTime('start_date')->nullable()->after('is_exam');
            $table->dateTime('end_date')->nullable()->after('start_date');
            $table->integer('grace_period')->nullable()->after('end_date');
            $table->boolean('randomize_options')->default(false)->after('grace_period');
            $table->boolean('prevent_copy_paste')->default(false)->after('randomize_options');
            $table->boolean('require_webcam')->default(false)->after('prevent_copy_paste');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->integer('tab_switch_count')->default(0)->after('status');
            $table->string('ip_address', 45)->nullable()->after('tab_switch_count');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['is_exam', 'start_date', 'end_date', 'grace_period', 'randomize_options', 'prevent_copy_paste', 'require_webcam']);
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['tab_switch_count', 'ip_address']);
        });
    }
};
