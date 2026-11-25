<?php

namespace App\Controllers;

use App\Services\PusherService;
use Core\ApiController;
use Core\PostResponseJson;

/**
 * Realtime (Pusher) endpoints for the Next.js frontend.
 *
 * GET  /api/realtime/config  public: whether realtime is on, plus the public key and cluster
 * POST /api/realtime/auth    signed-in + CSRF: signs a subscription to the caller's OWN
 *                            private channel only (private-chat.{their id})
 */
class RealtimeApiController extends ApiController
{
    public function config(): void
    {
        $pusher = new PusherService();
        $this->success([
            'enabled' => $pusher->isConfigured(),
            'key' => $pusher->isConfigured() ? $pusher->publicKey() : null,
            'cluster' => $pusher->cluster(),
        ]);
    }

    public function auth(): void
    {
        $user = $this->requireApiAuth();
        if ((int)($user['is_locked'] ?? 0) === 1) {
            $this->forbidden('Tài khoản đang bị khóa.');
        }
        $this->requireCsrf();
        $pusher = new PusherService();
        if (!$pusher->isConfigured()) {
            $this->error('REALTIME_DISABLED', 'Realtime chưa được cấu hình.', 503);
        }
        // pusher-js posts application/x-www-form-urlencoded; accept JSON too.
        $data = $_POST ?: $this->requestData();
        $socketId = is_scalar($data['socket_id'] ?? null) ? (string)$data['socket_id'] : '';
        $channel = is_scalar($data['channel_name'] ?? null) ? (string)$data['channel_name'] : '';
        if (!preg_match('/^\d+\.\d+$/', $socketId)) {
            $this->validationError('socket_id không hợp lệ.');
        }
        if ($channel !== PusherService::userChannel((int)$user['id'])) {
            $this->forbidden('Bạn chỉ được nghe kênh của chính mình.');
        }
        // Pusher expects the bare {"auth": "..."} object, not the API envelope.
        PostResponseJson::cleanOutputBuffers();
        // Explicit status: the POST normaliser otherwise treats an unset code as an error.
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['auth' => $pusher->authorizeChannel($socketId, $channel)]);
        exit;
    }
}
