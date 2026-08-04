<?php

namespace Tests\Unit;

use App\Models\Video;
use PHPUnit\Framework\TestCase;

class VideoSourceTest extends TestCase
{
    public function test_source_types_are_explicit_and_stable(): void
    {
        $this->assertSame('hls', Video::SOURCE_HLS);
        $this->assertSame('youtube', Video::SOURCE_YOUTUBE);
        $this->assertSame(['hls', 'youtube'], Video::SOURCE_TYPES);
    }

    public function test_video_without_source_type_remains_backward_compatible_with_hls(): void
    {
        $video = new Video;

        $this->assertTrue($video->isHls());
        $this->assertFalse($video->isYoutube());
    }

    public function test_youtube_source_and_metadata_can_be_mass_assigned(): void
    {
        $video = new Video([
            'source_type' => Video::SOURCE_YOUTUBE,
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->assertTrue($video->isYoutube());
        $this->assertFalse($video->isHls());
        $this->assertSame('dQw4w9WgXcQ', $video->youtube_video_id);
        $this->assertSame(
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            $video->youtube_url,
        );
    }
}
