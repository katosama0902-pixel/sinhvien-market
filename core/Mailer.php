<?php

namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

/**
 * Mailer - Utility to send emails via PHPMailer
 */
class Mailer
{
    /**
     * Gửi email OTP hoặc thông báo
     * Hỗ trợ 3 tham số: send($to, $subject, $body)
     * HOẶC 4 tham số: send($to, $name, $subject, $body)
     */
    public static function send(string $to, string $arg2, string $arg3, string $arg4 = null): bool
    {
        if ($arg4 !== null) {
            // 4 tham số: $to, $name, $subject, $body
            $toName = $arg2;
            $subject = $arg3;
            $body = $arg4;
        } else {
            // 3 tham số: $to, $subject, $body
            $toName = '';
            $subject = $arg2;
            $body = $arg3;
        }

        $mail = new PHPMailer(true);

        // Nạp cấu hình từ .env
        $host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $user = $_ENV['MAIL_USER'] ?? '';
        $pass = $_ENV['MAIL_PASS'] ?? '';
        $port = $_ENV['MAIL_PORT'] ?? 587;
        
        // Cố tình log hoặc giả lập nếu không có mật khẩu Gmail được cấu hình:
        if (empty($user) || empty($pass)) {
            // V6-001 fix: ghi vào storage/logs/ thay vì web root để tránh lộ OTP qua browser
            $logDir = ROOT . '/storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            $log = date('Y-m-d H:i:s') . " [MOCK MAIL]: To=$to, Subject=$subject\n";
            file_put_contents($logDir . '/mock_mail.log', $log, FILE_APPEND);
            return true; // Giả lập gửi thành công
        }

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';

            //Recipients
            $mail->setFrom($user, 'SinhVienMarket');
            if ($toName) {
                $mail->addAddress($to, $toName);
            } else {
                $mail->addAddress($to);
            }

            //Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mail error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
