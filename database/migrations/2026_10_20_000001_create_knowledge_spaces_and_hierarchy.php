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
        // 1. Create knowledge_spaces table (Confluence-style Spaces)
        Schema::create('knowledge_spaces', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable()->default('mdi-folder-outline');
            $table->string('color', 20)->nullable()->default('#4F46E5');
            $table->enum('visibility', ['public', 'internal', 'restricted'])->default('internal')->index();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_archived')->default(false)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        // 2. Add hierarchical tree and document expiry fields to articles table
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('space_id')->nullable()->after('tenant_id')->constrained('knowledge_spaces')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->after('space_id')->constrained('articles')->nullOnDelete();
            $table->integer('order')->default(0)->after('parent_id')->index();
            $table->integer('review_cycle_days')->default(180)->after('status');
            $table->timestamp('next_review_at')->nullable()->after('reviewed_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['space_id']);
            $table->dropColumn(['next_review_at', 'review_cycle_days', 'order', 'parent_id', 'space_id']);
        });

        Schema::dropIfExists('knowledge_spaces');
    }
};
