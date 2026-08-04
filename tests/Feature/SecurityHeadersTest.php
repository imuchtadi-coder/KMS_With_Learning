<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_web_pages_receive_restricted_youtube_content_security_policy(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString('frame-src https://www.youtube.com https://www.youtube-nocookie.com', $csp);
        $this->assertStringContainsString('https://www.youtube.com', $csp);
        $this->assertStringContainsString('https://s.ytimg.com', $csp);
        $this->assertStringContainsString("img-src 'self' data: https://ui-avatars.com", $csp);
        $this->assertStringNotContainsString("img-src 'self' data: https:;", $csp);
        $this->assertStringNotContainsString('*.youtube.com', $csp);
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_api_responses_keep_the_same_security_policy(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk();
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString(
            'frame-src https://www.youtube.com https://www.youtube-nocookie.com',
            $response->headers->get('Content-Security-Policy')
        );
    }
}
