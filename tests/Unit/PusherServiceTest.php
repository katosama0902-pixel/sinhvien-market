<?php

namespace Tests\Unit;

use App\Services\PusherService;
use PHPUnit\Framework\TestCase;

class PusherServiceTest extends TestCase
{
    private array $saved = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['PUSHER_APP_ID', 'PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_CLUSTER'] as $name) {
            $this->saved[$name] = [$_ENV[$name] ?? null, getenv($name)];
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->saved as $name => [$env, $process]) {
            if ($env === null) {
                unset($_ENV[$name]);
            } else {
                $_ENV[$name] = $env;
            }
            putenv($process === false ? $name : "$name=$process");
        }
        parent::tearDown();
    }

    private function configure(?array $values): void
    {
        foreach (['PUSHER_APP_ID', 'PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_CLUSTER'] as $name) {
            unset($_ENV[$name]);
            putenv($name);
        }
        foreach ($values ?? [] as $name => $value) {
            $_ENV[$name] = $value;
        }
    }

    public function test_private_channel_signature_matches_the_pusher_protocol_example(): void
    {
        // Example from the Pusher "authorizing users" documentation.
        $this->configure(['PUSHER_APP_ID' => '1', 'PUSHER_APP_KEY' => '278d425bdf160c739803',
            'PUSHER_APP_SECRET' => '7ad3773142a6692b25b8']);

        self::assertSame(
            '278d425bdf160c739803:58df8b0c36d6982b82c3ecf6b4662e34fe8c25bba48f5369f135bf843651c3a4',
            (new PusherService())->authorizeChannel('1234.1234', 'private-foobar')
        );
    }

    public function test_chat_events_use_private_per_user_channels(): void
    {
        self::assertSame('private-chat.42', PusherService::userChannel(42));
    }

    public function test_without_credentials_nothing_is_sent_and_no_secret_is_built_in(): void
    {
        $this->configure(null);
        $pusher = new PusherService();

        self::assertFalse($pusher->isConfigured());
        self::assertSame('', $pusher->publicKey());
        self::assertFalse($pusher->trigger(PusherService::userChannel(1), 'new_message', ['x' => 1]));
    }
}
