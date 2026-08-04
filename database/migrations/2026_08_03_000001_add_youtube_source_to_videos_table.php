<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('source_type', 20)
                ->default('hls')
                ->after('title');
            $table->string('youtube_video_id', 32)
                ->nullable()
                ->after('source_type');
            $table->text('youtube_url')
                ->nullable()
                ->after('youtube_video_id');

            $table->index(['source_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'status']);
            $table->dropColumn([
                'source_type',
                'youtube_video_id',
                'youtube_url',
            ]);
        });
    }
};
