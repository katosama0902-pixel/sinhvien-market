<?php

namespace Core;

final class PostResponseJson
{
    private static bool $active = false;

    public static function start(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || self::$active) {
            return;
        }

        self::$active = true;
        ob_start([self::class, 'normalizeOutput']);
    }

    public static function isActive(): bool
    {
        return self::$active;
    }

    public static function cleanOutputBuffers(): void
    {
        if (!self::$active) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            return;
        }

        while (ob_get_level() > 1) {
            ob_end_clean();
        }
        if (ob_get_level() === 1) {
            ob_clean();
        }
    }

    public static function flush(): void
    {
        if (self::$active && ob_get_level() === 1) {
            ob_end_flush();
            self::$active = false;
        }
    }

    /**
     * @return array{status: int, body: string}
     */
    public static function normalizeResponse(string $body, int $status, ?string $location = null): array
    {
        if ($status === 204 && trim($body) === '' && $location === null) {
            return [
                'status' => 200,
                'body' => json_encode([
                    'success' => true,
                    'data' => null,
                    'message' => 'Yêu cầu đã được xử lý thành công.',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];
        }

        json_decode($body);
        $isJson = json_last_error() === JSON_ERROR_NONE && trim($body) !== '';

        if ($isJson && $location === null && ($status < 300 || $status >= 400)) {
            return ['status' => $status === 204 ? 200 : $status, 'body' => $body];
        }

        $responseStatus = $status;
        if ($status >= 300 && $status < 400) {
            $responseStatus = 409;
        } elseif ($status >= 200 && $status < 300) {
            $responseStatus = 500;
        }

        $error = $location !== null ? 'POST_REDIRECT' : 'POST_RESPONSE_NOT_JSON';
        $message = $location !== null
            ? 'Yêu cầu POST không được trả redirect. Hãy gửi yêu cầu qua API JSON của Next.js.'
            : 'Máy chủ không trả phản hồi JSON cho yêu cầu POST.';
        $payload = [
            'success' => false,
            'error' => [
                'code' => $error,
                'message' => $message,
            ],
        ];

        $redirectTo = self::safeLocalRedirect($location);
        if ($redirectTo !== null) {
            $payload['error']['meta'] = ['redirectTo' => $redirectTo];
        }

        return [
            'status' => $responseStatus,
            'body' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    public static function normalizeOutput(string $buffer, int $phase): string
    {
        if ((($phase & PHP_OUTPUT_HANDLER_FINAL) === 0) || !self::$active) {
            return $buffer;
        }

        $status = http_response_code();
        $status = is_int($status) ? $status : 200;
        $location = null;
        foreach (headers_list() as $header) {
            if (stripos($header, 'Location:') === 0) {
                $location = trim(substr($header, strlen('Location:')));
                break;
            }
        }

        $response = self::normalizeResponse($buffer, $status, $location);
        self::$active = false;
        if (!headers_sent()) {
            header_remove('Location');
            header_remove('Content-Length');
            header_remove('Content-Disposition');
            header_remove('Content-Encoding');
            http_response_code($response['status']);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');
            header('X-Content-Type-Options: nosniff');
        }

        return $response['body'];
    }

    private static function safeLocalRedirect(?string $location): ?string
    {
        if ($location === null || $location === '') {
            return null;
        }
        if (str_starts_with($location, '/') && !str_starts_with($location, '//')) {
            return $location;
        }

        $target = parse_url($location);
        $appUrl = parse_url((string)($_ENV['APP_URL'] ?? ''));
        if (
            $target === false
            || $appUrl === false
            || !isset($target['host'], $appUrl['host'])
            || strtolower($target['host']) !== strtolower($appUrl['host'])
            || strtolower((string)($target['scheme'] ?? '')) !== strtolower((string)($appUrl['scheme'] ?? ''))
            || (int)($target['port'] ?? 0) !== (int)($appUrl['port'] ?? 0)
        ) {
            return null;
        }

        $path = $target['path'] ?? '/';
        return $path . (isset($target['query']) ? '?' . $target['query'] : '');
    }
}
