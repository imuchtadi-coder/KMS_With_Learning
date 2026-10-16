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
        // Use DB statement for PostgreSQL specific tsvector type and GIN index
        DB::statement("ALTER TABLE articles ADD COLUMN IF NOT EXISTS search_vector tsvector;");

        // 2. Create trigger function for automatic updating of search_vector
        DB::unprepared("
            CREATE OR REPLACE FUNCTION articles_search_vector_update() RETURNS trigger AS $$
            BEGIN
                NEW.search_vector :=
                    setweight(to_tsvector('simple', coalesce(NEW.title, '')), 'A') ||
                    setweight(to_tsvector('simple', coalesce(NEW.excerpt, '')), 'B') ||
                    setweight(to_tsvector('simple', coalesce(regexp_replace(NEW.content, '<[^>]*>', ' ', 'g'), '')), 'C');
                RETURN NEW;
            END
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS trg_articles_search_vector ON articles;
            CREATE TRIGGER trg_articles_search_vector
                BEFORE INSERT OR UPDATE OF title, excerpt, content
                ON articles
                FOR EACH ROW
                EXECUTE FUNCTION articles_search_vector_update();
        ");

        // 3. Populate existing rows
        DB::statement("
            UPDATE articles SET
                search_vector =
                    setweight(to_tsvector('simple', coalesce(title, '')), 'A') ||
                    setweight(to_tsvector('simple', coalesce(excerpt, '')), 'B') ||
                    setweight(to_tsvector('simple', coalesce(regexp_replace(content, '<[^>]*>', ' ', 'g'), '')), 'C');
        ");

        // 4. Create GIN index for blazing fast full-text search
        DB::statement("CREATE INDEX IF NOT EXISTS articles_search_vector_gin_idx ON articles USING gin(search_vector);");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS articles_search_vector_gin_idx;");
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_articles_search_vector ON articles;
            DROP FUNCTION IF EXISTS articles_search_vector_update();
        ");
        DB::statement("ALTER TABLE articles DROP COLUMN IF EXISTS search_vector;");
    }
};
