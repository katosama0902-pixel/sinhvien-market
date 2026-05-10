<?php

namespace App\Services;

/**
 * MoMo Sandbox Service (v2)
 * Tài liệu chính thức: https://developers.momo.vn/v3/docs/payment/api/
 *
 * Cấu hình credentials trong file .env:
 *   MOMO_PARTNER_CODE, MOMO_ACCESS_KEY, MOMO_SECRET_KEY, MOMO_ENDPOINT
 *
 * Số điện thoại test sandbox: 0000000000 | OTP: 000000
 */
class MomoService
{
    // ─── BUG-013 fix: đọc credentials từ .env thay vì hardcode ──────────────────
    private static function partnerCode(): string { return $_ENV['MOMO_PARTNER_CODE'] ?? ''; }
    private static function accessKey(): string   { return $_ENV['MOMO_ACCESS_KEY']   ?? ''; }
    private static function secretKey(): string   { return $_ENV['MOMO_SECRET_KEY']   ?? ''; }
    private static function endpoint(): string    { return $_ENV['MOMO_ENDPOINT']     ?? ''; }


    /**
     * Tạo yêu cầu thanh toán MoMo Sandbox.
     * MoMo dùng luồng API (POST) để lấy payUrl, sau đó redirect user.
     *
     * @param int    $txnRef   ID giao dịch nội bộ
     * @param int    $amount   Số tiền (VND)
     * @param string $orderInfo Mô tả đơn hàng
     * @return array{success: bool, payUrl: string, message: string}
     */
    public static function createPayment(int $txnRef, int $amount, string $orderInfo): array
    {
        $appUrl      = rtrim($_ENV['APP_URL'] ?? '', '/');
        $returnUrl   = $appUrl . '/transactions/momo-return';
        $notifyUrl   = $appUrl . '/transactions/momo-ipn';
        $requestId   = self::partnerCode() . '_' . $txnRef . '_' . time();
        $orderId     = 'SVMkt_' . $txnRef . '_' . time();
        $requestType = 'payWithATM';
        $extraData   = '';

        // Chuỗi ký theo spec MoMo v2 (chú ý thứ tự cố định)
        $rawSignature = "accessKey=" . self::accessKey()
            . "&amount={$amount}"
            . "&extraData={$extraData}"
            . "&ipnUrl={$notifyUrl}"
            . "&orderId={$orderId}"
            . "&orderInfo={$orderInfo}"
            . "&partnerCode=" . self::partnerCode()
            . "&redirectUrl={$returnUrl}"
            . "&requestId={$requestId}"
            . "&requestType={$requestType}";

        $signature = hash_hmac('sha256', $rawSignature, self::secretKey());

        $payload = [
            'partnerCode' => self::partnerCode(),
            'accessKey'   => self::accessKey(),
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $returnUrl,
            'ipnUrl'      => $notifyUrl,
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature,
            'lang'        => 'vi',
        ];

        // Gọi MoMo API để lấy payUrl
        $ch = curl_init(self::endpoint());
        $isLocal = in_array($_ENV['APP_ENV'] ?? 'production', ['local', 'development']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => !$isLocal,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'payUrl' => '', 'message' => 'Lỗi kết nối MoMo: ' . $curlError];
        }

        $data = json_decode($response, true) ?? [];
        $resultCode = (int)($data['resultCode'] ?? -1);

        if ($resultCode === 0 && !empty($data['payUrl'])) {
            return ['success' => true, 'payUrl' => $data['payUrl'], 'message' => 'OK'];
        }

        $message = $data['message'] ?? 'Có lỗi xảy ra với thanh toán MoMo.';
        return ['success' => false, 'payUrl' => '', 'message' => $message];
    }

    /**
     * Xác minh chữ ký callback từ MoMo gửi về.
     */
    public static function verifySignature(array $data): bool
    {
        $received = $data['signature'] ?? '';

        $rawSignature = "accessKey=" . self::accessKey()
            . "&amount={$data['amount']}"
            . "&extraData={$data['extraData']}"
            . "&message={$data['message']}"
            . "&orderId={$data['orderId']}"
            . "&orderInfo={$data['orderInfo']}"
            . "&orderType={$data['orderType']}"
            . "&partnerCode={$data['partnerCode']}"
            . "&payType={$data['payType']}"
            . "&requestId={$data['requestId']}"
            . "&responseTime={$data['responseTime']}"
            . "&resultCode={$data['resultCode']}"
            . "&transId={$data['transId']}";

        $expected = hash_hmac('sha256', $rawSignature, self::secretKey());
        return hash_equals($expected, $received);
    }

    /**
     * Kiểm tra thanh toán có thành công không.
     * resultCode = 0 nghĩa là thành công.
     */
    public static function isSuccess(array $data): bool
    {
        return ((int)($data['resultCode'] ?? -1)) === 0;
    }
}
