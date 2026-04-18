<?php

namespace App\Services;

class GoogleAiService
{
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    /**
     * Send a prompt to Google Gemini API and return the response string.
     */
    public static function askGemini(string $prompt): string
    {
        $apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
        if (empty($apiKey)) {
            return "Xin lỗi, hiện tại Trợ lý AI đang không thể kết nối với máy chủ.";
        }

        $url = self::API_URL . '?key=' . $apiKey;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            // Thêm các tham số cẩn thận để AI nói chuyện giống một người thật
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 150,
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        // Để tránh delay đồ án quá lâu và timeout, set tối đa 10 giây
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // SSL verify: tắt chỉ ở môi trường local (Laragon), BẮT BUỘC bật khi production
        $isLocal = in_array($_ENV['APP_ENV'] ?? 'production', ['local', 'development']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$isLocal);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return "Lỗi kết nối Trợ lý AI: " . $error;
        }

        $data = json_decode($response, true);
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($data['candidates'][0]['content']['parts'][0]['text']);
        }

        if (isset($data['error']['message'])) {
            return "Lỗi API AI: " . $data['error']['message'];
        }

        return "Xin lỗi, Trợ lý AI hiện đang gặp trục trặc khi suy nghĩ.";
    }
}
