<?php

namespace Tests\Unit;

use Core\PostResponseJson;
use PHPUnit\Framework\TestCase;

class PostResponseJsonTest extends TestCase
{
    public function test_preserves_json_responses(): void
    {
        $response = PostResponseJson::normalizeResponse(
            '{"success":true,"data":{"id":7}}',
            201
        );

        self::assertSame(201, $response['status']);
        self::assertSame('{"success":true,"data":{"id":7}}', $response['body']);
    }

    public function test_converts_html_responses_to_json_errors(): void
    {
        $response = PostResponseJson::normalizeResponse('<html><body>Failure</body></html>', 200);
        $payload = json_decode($response['body'], true);

        self::assertSame(500, $response['status']);
        self::assertSame('POST_RESPONSE_NOT_JSON', $payload['error']['code']);
    }

    public function test_converts_empty_no_content_responses_to_json_success(): void
    {
        $response = PostResponseJson::normalizeResponse('', 204);
        $payload = json_decode($response['body'], true);

        self::assertSame(200, $response['status']);
        self::assertTrue($payload['success']);
    }

    public function test_converts_redirects_to_json_errors_with_safe_local_target(): void
    {
        $response = PostResponseJson::normalizeResponse(
            '',
            302,
            '/transactions/history'
        );
        $payload = json_decode($response['body'], true);

        self::assertSame(409, $response['status']);
        self::assertSame('POST_REDIRECT', $payload['error']['code']);
        self::assertSame('/transactions/history', $payload['error']['meta']['redirectTo']);
    }

    public function test_does_not_expose_external_redirect_targets(): void
    {
        $response = PostResponseJson::normalizeResponse('', 302, 'https://payments.example.test/checkout');
        $payload = json_decode($response['body'], true);

        self::assertSame(409, $response['status']);
        self::assertArrayNotHasKey('meta', $payload['error']);
    }
}
