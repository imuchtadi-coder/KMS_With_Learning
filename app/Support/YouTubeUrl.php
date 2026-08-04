<?php

namespace App\Support;

use InvalidArgumentException;

final class YouTubeUrl
{
    private const VIDEO_ID_PATTERN = '/^[A-Za-z0-9_-]{11}$/D';

    private const YOUTUBE_HOSTS = [
        'youtube.com',
        'www.youtube.com',
        'm.youtube.com',
    ];

    private const SHORT_HOSTS = [
        'youtu.be',
        'www.youtu.be',
    ];

    private const NO_COOKIE_HOSTS = [
        'youtube-nocookie.com',
        'www.youtube-nocookie.com',
    ];

    public static function videoId(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            throw new InvalidArgumentException('URL YouTube wajib diisi.');
        }

        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            throw new InvalidArgumentException('Format URL YouTube tidak valid.');
        }

        if (strtolower($parts['scheme']) !== 'https') {
            throw new InvalidArgumentException('URL YouTube harus menggunakan HTTPS.');
        }

        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            throw new InvalidArgumentException('URL YouTube mengandung komponen yang tidak diizinkan.');
        }

        $host = strtolower($parts['host']);
        $path = trim($parts['path'] ?? '', '/');
        $videoId = null;

        if (in_array($host, self::SHORT_HOSTS, true)) {
            $videoId = explode('/', $path)[0] ?? null;
        } elseif (in_array($host, self::YOUTUBE_HOSTS, true)) {
            $videoId = self::videoIdFromYoutubePath($path, $parts['query'] ?? null);
        } elseif (in_array($host, self::NO_COOKIE_HOSTS, true)) {
            $videoId = self::videoIdFromPrefixedPath($path, 'embed');
        } else {
            throw new InvalidArgumentException('Domain URL bukan domain YouTube yang diizinkan.');
        }

        if (! is_string($videoId) || preg_match(self::VIDEO_ID_PATTERN, $videoId) !== 1) {
            throw new InvalidArgumentException('ID video YouTube tidak valid.');
        }

        return $videoId;
    }

    public static function embedUrl(string $videoId): string
    {
        if (preg_match(self::VIDEO_ID_PATTERN, $videoId) !== 1) {
            throw new InvalidArgumentException('ID video YouTube tidak valid.');
        }

        return 'https://www.youtube-nocookie.com/embed/'.$videoId;
    }

    public static function canonicalUrl(string $videoId): string
    {
        if (preg_match(self::VIDEO_ID_PATTERN, $videoId) !== 1) {
            throw new InvalidArgumentException('ID video YouTube tidak valid.');
        }

        return 'https://www.youtube.com/watch?v='.$videoId;
    }

    private static function videoIdFromYoutubePath(string $path, ?string $query): ?string
    {
        if ($path === 'watch') {
            parse_str($query ?? '', $parameters);

            return is_string($parameters['v'] ?? null) ? $parameters['v'] : null;
        }

        foreach (['shorts', 'embed'] as $prefix) {
            $videoId = self::videoIdFromPrefixedPath($path, $prefix);

            if ($videoId !== null) {
                return $videoId;
            }
        }

        return null;
    }

    private static function videoIdFromPrefixedPath(string $path, string $prefix): ?string
    {
        $segments = explode('/', $path);

        if (count($segments) !== 2 || $segments[0] !== $prefix) {
            return null;
        }

        return $segments[1];
    }
}
