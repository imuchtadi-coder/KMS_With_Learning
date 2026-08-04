<?php

namespace Tests\Unit;

use App\Support\YouTubeUrl;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class YouTubeUrlTest extends TestCase
{
    #[DataProvider('validUrlProvider')]
    public function test_it_extracts_video_id_from_supported_urls(string $url): void
    {
        $this->assertSame('dQw4w9WgXcQ', YouTubeUrl::videoId($url));
    }

    public static function validUrlProvider(): array
    {
        return [
            'watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            'watch with parameters' => ['https://youtube.com/watch?feature=shared&v=dQw4w9WgXcQ&t=10'],
            'mobile watch' => ['https://m.youtube.com/watch?v=dQw4w9WgXcQ'],
            'short link' => ['https://youtu.be/dQw4w9WgXcQ'],
            'short link with query' => ['https://www.youtu.be/dQw4w9WgXcQ?si=abc123'],
            'shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ'],
            'embed' => ['https://www.youtube.com/embed/dQw4w9WgXcQ'],
            'privacy enhanced embed' => ['https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
            'surrounding whitespace' => ['  https://www.youtube.com/watch?v=dQw4w9WgXcQ  '],
        ];
    }

    #[DataProvider('invalidUrlProvider')]
    public function test_it_rejects_invalid_or_untrusted_urls(string $url): void
    {
        $this->expectException(InvalidArgumentException::class);

        YouTubeUrl::videoId($url);
    }

    public static function invalidUrlProvider(): array
    {
        return [
            'empty' => [''],
            'not a url' => ['dQw4w9WgXcQ'],
            'http' => ['http://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            'wrong domain' => ['https://example.com/watch?v=dQw4w9WgXcQ'],
            'youtube suffix attack' => ['https://youtube.com.example.org/watch?v=dQw4w9WgXcQ'],
            'youtube prefix attack' => ['https://evilyoutube.com/watch?v=dQw4w9WgXcQ'],
            'untrusted subdomain' => ['https://evil.youtube.com/watch?v=dQw4w9WgXcQ'],
            'credentials' => ['https://user:pass@youtube.com/watch?v=dQw4w9WgXcQ'],
            'custom port' => ['https://youtube.com:8443/watch?v=dQw4w9WgXcQ'],
            'missing id' => ['https://www.youtube.com/watch'],
            'short id' => ['https://youtu.be/abc123'],
            'long id' => ['https://youtu.be/dQw4w9WgXcQextra'],
            'invalid id characters' => ['https://youtu.be/dQw4w9WgX!Q'],
            'unsupported path' => ['https://www.youtube.com/channel/dQw4w9WgXcQ'],
            'extra embed segment' => ['https://www.youtube.com/embed/dQw4w9WgXcQ/extra'],
            'no-cookie watch path' => ['https://www.youtube-nocookie.com/watch?v=dQw4w9WgXcQ'],
        ];
    }

    public function test_it_builds_privacy_enhanced_embed_url(): void
    {
        $this->assertSame(
            'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            YouTubeUrl::embedUrl('dQw4w9WgXcQ'),
        );
    }

    public function test_it_builds_canonical_url(): void
    {
        $this->assertSame(
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            YouTubeUrl::canonicalUrl('dQw4w9WgXcQ'),
        );
    }

    #[DataProvider('invalidIdProvider')]
    public function test_it_rejects_invalid_ids_when_building_urls(string $videoId): void
    {
        $this->expectException(InvalidArgumentException::class);

        YouTubeUrl::embedUrl($videoId);
    }

    public static function invalidIdProvider(): array
    {
        return [
            'empty' => [''],
            'short' => ['abc123'],
            'too long' => ['dQw4w9WgXcQextra'],
            'invalid characters' => ['dQw4w9WgX!Q'],
        ];
    }
}
