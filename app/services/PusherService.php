<?php

namespace App\Services;

/**
 * PusherService (cURL wrapper, no Composer dependency).
 *
 * Realtime chat uses PRIVATE channels `private-chat.{userId}`: the browser can
 * only subscribe after POST /api/realtime/auth signs the subscription for the
 * signed-in user (see RealtimeApiController). Public `chat.{id}` channels were
 * readable by anyone who knew a user id, since the app key is public.
 *
 * Credentials come only from the environment (PUSHER_APP_ID / _KEY / _SECRET /
 * _CLUSTER). When they are missing, trigger() is a no-op and the frontend falls
 * back to polling.
 */
class PusherService
{
    private string $appId;
    private string $key;
    private string $secret;
    private string $cluster;

    public function __construct()
    {
        $this->appId = self::env('PUSHER_APP_ID');
        $this->key = self::env('PUSHER_APP_KEY');
        $this->secret = self::env('PUSHER_APP_SECRET');
        $this->cluster = self::env('PUSHER_APP_CLUSTER') ?: 'ap1';
    }

    /** Private channel that carries one user's chat events. */
    public static function userChannel(int $userId): string
    {
        return 'private-chat.' . $userId;
    }

    public function isConfigured(): bool
    {
        return $this->appId !== '' && $this->key !== '' && $this->secret !== '';
    }

    public function publicKey(): string
    {
        return $this->key;
    }

    public function cluster(): string
    {
        return $this->cluster;
    }

    /**
     * Signature for a private-channel subscription (Pusher protocol):
     * "<key>:" . HMAC-SHA256(secret, "<socket_id>:<channel_name>").
     */
    public function authorizeChannel(string $socketId, string $channel): string
    {
        return $this->key . ':' . hash_hmac('sha256', $socketId . ':' . $channel, $this->secret);
    }

    /**
     * Send an event to a channel.
     *
     * @param string $channel e.g. PusherService::userChannel(12)
     * @param string $event   e.g. 'new_message'
     * @param mixed  $data    array or string payload
     * @return bool true when Pusher accepted the event
     */
    public function trigger(string $channel, string $event, $data): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }
        $host = "api-{$this->cluster}.pusher.com";
        $path = "/apps/{$this->appId}/events";

        $body = json_encode([
            'name'     => $event,
            'channels' => [$channel],
            'data'     => is_string($data) ? $data : json_encode($data)
        ]);

        $params = [
            'auth_key'       => $this->key,
            'auth_timestamp' => time(),
            'auth_version'   => '1.0',
            'body_md5'       => md5($body)
        ];
        ksort($params);

        $queryString = http_build_query($params, '', '&');
        // Ký HMAC SHA256
        $stringToSign = "POST\n{$path}\n{$queryString}";
        $queryString .= '&auth_signature=' . hash_hmac('sha256', $stringToSign, $this->secret);

        $ch = curl_init("https://{$host}{$path}?{$queryString}");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT        => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false || $httpCode !== 200) {
            error_log('Pusher trigger failed: HTTP ' . $httpCode . ' ' . curl_error($ch));
        }
        return $httpCode === 200;
    }

    private static function env(string $name): string
    {
        $value = $_ENV[$name] ?? getenv($name);
        return is_string($value) ? trim($value) : '';
    }
}
