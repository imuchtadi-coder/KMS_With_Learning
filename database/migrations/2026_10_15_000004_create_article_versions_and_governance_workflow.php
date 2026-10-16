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
        // 1. Upgrade articles table with versioning and SME review fields
        Schema::table('articles', function (Blueprint $table) {
            $table->string('version', 20)->default('1.0')->after('slug');
            $table->text('review_notes')->nullable()->after('status');
            $table->foreignId('reviewed_by')->nullable()->after('review_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });

        // Update PostgreSQL enum check constraint to include pending_review and rejected
        DB::statement("ALTER TABLE articles DROP CONSTRAINT IF EXISTS articles_status_check;");
        DB::statement("ALTER TABLE articles ADD CONSTRAINT articles_status_check CHECK (status IN ('draft', 'pending_review', 'published', 'rejected', 'archived'));");

        // 2. Create article_versions table
        Schema::create('article_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->string('version', 20)->default('1.0');
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->text('change_summary')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['article_id', 'version']);
        });

        // 3. Create initial version snapshot for existing published articles
        $existingArticles = DB::table('articles')->get();
        foreach ($existingArticles as $article) {
            DB::table('article_versions')->insert([
                'article_id' => $article->id,
                'version' => '1.0',
                'title' => $article->title,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'change_summary' => 'Versi awal dokumen (Inisialisasi sistem)',
                'created_by' => $article->author_id,
                'reviewed_by' => $article->author_id,
                'reviewed_at' => $article->published_at ?? now(),
                'created_at' => $article->created_at ?? now(),
                'updated_at' => $article->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_versions');

        DB::statement("ALTER TABLE articles DROP CONSTRAINT IF EXISTS articles_status_check;");
        DB::statement("ALTER TABLE articles ADD CONSTRAINT articles_status_check CHECK (status IN ('draft', 'published', 'archived'));");

        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['reviewed_at', 'reviewed_by', 'review_notes', 'version']);
        });
    }
};
