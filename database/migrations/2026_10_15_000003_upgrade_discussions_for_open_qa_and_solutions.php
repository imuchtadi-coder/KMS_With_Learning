<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Upgrade discussions table
        Schema::table('discussions', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->index()->after('id');
            $table->foreignId('category_id')->nullable()->after('lesson_id')->constrained('categories')->nullOnDelete();
            $table->boolean('is_resolved')->default(false)->index()->after('is_locked');
            $table->unsignedBigInteger('accepted_reply_id')->nullable()->after('is_resolved');
        });

        // Make course_id nullable in PostgreSQL
        DB::statement("ALTER TABLE discussions ALTER COLUMN course_id DROP NOT NULL;");

        // Backfill tenant_id for existing discussions from courses
        DB::statement("
            UPDATE discussions
            SET tenant_id = courses.tenant_id
            FROM courses
            WHERE discussions.course_id = courses.id
            AND discussions.tenant_id IS NULL;
        ");

        // 2. Upgrade discussion_replies table
        Schema::table('discussion_replies', function (Blueprint $table) {
            $table->boolean('is_solution')->default(false)->index()->after('content');
            $table->timestamp('verified_at')->nullable()->after('is_solution');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
        });

        // Add foreign key constraint for accepted_reply_id on discussions
        Schema::table('discussions', function (Blueprint $table) {
            $table->foreign('accepted_reply_id')
                ->references('id')
                ->on('discussion_replies')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropForeign(['accepted_reply_id']);
            $table->dropColumn(['accepted_reply_id', 'is_resolved', 'category_id', 'tenant_id']);
        });

        Schema::table('discussion_replies', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verified_by', 'verified_at', 'is_solution']);
        });

        DB::statement("ALTER TABLE discussions ALTER COLUMN course_id SET NOT NULL;");
    }
};
