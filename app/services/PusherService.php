<?php

namespace App\Services;

/**
 * PusherService (cURL Wrapper)
 * Dùng để trigger event WebSocket qua HTTP API của Pusher mà không cần thư viện Composer.
 */
class PusherService
{
    private string $appId;
    private string $key;
    private string $secret;
    private string $cluster;

    public function __construct()
    {
        $this->appId   = getenv('PUSHER_APP_ID') ?: '1858547';
        $this->key     = getenv('PUSHER_APP_KEY') ?: 'f50f24f5a8a8c171e1b1';
        $this->secret  = getenv('PUSHER_APP_SECRET') ?: '21b764b8893d5a2d6771';
        $this->cluster = getenv('PUSHER_APP_CLUSTER') ?: 'ap1';
    }

    /**
     * Gửi tin nhắn tới một kênh
     *
     * @param string $channel Tên kênh (vd: 'chat.12')
     * @param string $event   Tên sự kiện (vd: 'new_message')
     * @param mixed  $data    Dữ liệu cần gửi (mảng, chuỗi...)
     * @return bool True nếu gửi thành công
     */
    public function trigger(string $channel, string $event, $data): bool
    {
        $host = "api-{$this->cluster}.pusher.com";
        $path = "/apps/{$this->appId}/events";

        $body = json_encode([
            'name'     => $event,
            'channels' => [$channel],
            'data'     => is_string($data) ? $data : json_encode($data)
        ]);

        $bodyMd5 = md5($body);

        $params = [
            'auth_key'       => $this->key,
            'auth_timestamp' => time(),
            'auth_version'   => '1.0',
            'body_md5'       => $bodyMd5
        ];

        ksort($params);

        $queryString = http_build_query($params, '', '&');
        // Ký HMAC SHA256
        $stringToSign = "POST\n{$path}\n{$queryString}";
        $authSignature = hash_hmac('sha256', $stringToSign, $this->secret);

        $queryString .= '&auth_signature=' . $authSignature;

        $url = "https://{$host}{$path}?{$queryString}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_SSL_VERIFYPEER => false // Có thể tắt trong môi trường local
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return $httpCode === 200;
    }
}
