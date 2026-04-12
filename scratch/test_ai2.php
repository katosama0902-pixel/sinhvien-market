<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT', realpath(__DIR__ . '/..'));

// manually load Database.php and GoogleAiService.php
require_once __DIR__ . '/../config/Database.php';
\Config\Database::getInstance(); // loads ENV

require_once __DIR__ . '/../app/services/GoogleAiService.php';

$prompt = "Chào cậu, thử sinh ra 1 câu trả lời 30 chữ xem được không?";
echo "Sending to Gemini...\n";
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $_ENV['GEMINI_API_KEY'];
$payload = [
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

echo "Response: \n" . $response;
