<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->index('tenant_id');
        });
        Schema::table('live_classes', function (Blueprint $table) {
            $table->index('tenant_id');
        });
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->index('tenant_id');
        });
        Schema::table('learning_path_courses', function (Blueprint $table) {
            $table->index('tenant_id');
        });
        Schema::table('learning_path_enrollments', function (Blueprint $table) {
            $table->index('tenant_id');
        });
        Schema::table('learning_path_progress', function (Blueprint $table) {
            $table->index('tenant_id');
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->index('tenant_id');
        });
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->index('status');
        });
        Schema::table('quizzes', function (Blueprint $table) {
            $table->index('published');
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });
        Schema::table('live_classes', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });
        Schema::table('learning_path_courses', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });
        Schema::table('learning_path_enrollments', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });
        Schema::table('learning_path_progress', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['published']);
        });
    }
};
