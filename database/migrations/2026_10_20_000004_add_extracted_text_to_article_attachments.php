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
        Schema::table('article_attachments', function (Blueprint $table) {
            $table->longText('extracted_text')->nullable()->after('file_size');
        });

        // Add search_vector to article_attachments for deep full-text indexing inside files
        DB::statement("ALTER TABLE article_attachments ADD COLUMN IF NOT EXISTS search_vector tsvector;");

        // Trigger for article_attachments search_vector
        DB::unprepared("
            CREATE OR REPLACE FUNCTION attachments_search_vector_update() RETURNS trigger AS $$
            BEGIN
                NEW.search_vector :=
                    setweight(to_tsvector('simple', coalesce(NEW.title, '')), 'A') ||
                    setweight(to_tsvector('simple', coalesce(NEW.extracted_text, '')), 'B');
                RETURN NEW;
            END
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS trg_attachments_search_vector ON article_attachments;
            CREATE TRIGGER trg_attachments_search_vector
                BEFORE INSERT OR UPDATE OF title, extracted_text
                ON article_attachments
                FOR EACH ROW
                EXECUTE FUNCTION attachments_search_vector_update();
        ");

        DB::statement("CREATE INDEX IF NOT EXISTS attachments_search_vector_gin_idx ON article_attachments USING gin(search_vector);");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS attachments_search_vector_gin_idx;");
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_attachments_search_vector ON article_attachments;
            DROP FUNCTION IF EXISTS attachments_search_vector_update();
        ");
        DB::statement("ALTER TABLE article_attachments DROP COLUMN IF EXISTS search_vector;");

        Schema::table('article_attachments', function (Blueprint $table) {
            $table->dropColumn('extracted_text');
        });
    }
};
