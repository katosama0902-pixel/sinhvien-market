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
     * $to: email người nhận
     * $subject: tiêu đề
     * $body: nội dung html
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);

        // Nạp cấu hình từ .env
        $host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $user = $_ENV['MAIL_USER'] ?? '';
        $pass = $_ENV['MAIL_PASS'] ?? '';
        $port = $_ENV['MAIL_PORT'] ?? 587;
        
        // Cố tình log hoặc giả lập nếu không có mật khẩu Gmail được cấu hình:
        if (empty($user) || empty($pass)) {
            $log = date('Y-m-d H:i:s') . " [MOCK MAIL]: To=$to, Subject=$subject, Body=$body\n";
            file_put_contents(__DIR__ . '/../mock_mail.log', $log, FILE_APPEND);
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
            $mail->addAddress($to);

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
