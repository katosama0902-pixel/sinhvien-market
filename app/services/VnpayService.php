<?php

namespace App\Services;

/**
 * VNPay Sandbox Service
 * Tài liệu chính thức: https://sandbox.vnpayment.vn/apis/docs/
 *
 * Thông tin Sandbox (Demo):
 *   Terminal Code : DEMOV210
 *   Secret Key    : QWERTY1234567890
 *   Sandbox URL   : https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
 *
 * Thẻ test:
 *   Ngân hàng  : NCB
 *   Số thẻ    : 9704198526191432198
 *   Chủ thẻ   : NGUYEN VAN A
 *   Ngày phát : 07/15
 *   OTP       : 123456
 */
class VnpayService
{
    // ─── Sandbox Credentials ────────────────────────────────────────────────────
    private const TMN_CODE   = 'DEMOV210';
    private const HASH_SECRET = 'QWERTY1234567890';
    private const PAYMENT_URL = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
    private const VERSION     = '2.1.0';

    /**
     * Tạo URL thanh toán VNPay, redirect người dùng đến trang thanh toán.
     *
     * @param int    $txnRef   ID giao dịch nội bộ
     * @param int    $amount   Số tiền (VND)
     * @param string $orderInfo Mô tả đơn hàng
     * @return string URL thanh toán đã ký
     */
    public static function buildPaymentUrl(int $txnRef, int $amount, string $orderInfo): string
    {
        $returnUrl = rtrim($_ENV['APP_URL'] ?? '', '/') . '/transactions/vnpay-return';
        $createDate = date('YmdHis');
        $expireDate = date('YmdHis', strtotime('+15 minutes'));
        $ipAddr     = self::getClientIp();

        $inputData = [
            'vnp_Version'    => self::VERSION,
            'vnp_TmnCode'    => self::TMN_CODE,
            'vnp_Amount'     => $amount * 100, // VNPay tính theo đơn vị nhỏ nhất (x100)
            'vnp_Command'    => 'pay',
            'vnp_CreateDate' => $createDate,
            'vnp_CurrCode'   => 'VND',
            'vnp_IpAddr'     => $ipAddr,
            'vnp_Locale'     => 'vn',
            'vnp_OrderInfo'  => $orderInfo,
            'vnp_OrderType'  => 'other',
            'vnp_ReturnUrl'  => $returnUrl,
            'vnp_TxnRef'     => $txnRef,
            'vnp_ExpireDate' => $expireDate,
        ];

        // Sắp xếp theo key (bắt buộc trong spec VNPay)
        ksort($inputData);
        $queryString = http_build_query($inputData, '', '&', PHP_QUERY_RFC3986);

        // Ký bằng HMAC-SHA512
        $vnpSecureHash = hash_hmac('sha512', $queryString, self::HASH_SECRET);

        return self::PAYMENT_URL . '?' . $queryString . '&vnp_SecureHash=' . $vnpSecureHash;
    }

    /**
     * Xác minh chữ ký từ VNPay gửi về.
     * Trả về true nếu hợp lệ.
     */
    public static function verifyReturnSignature(array $vnpData): bool
    {
        $secureHash = $vnpData['vnp_SecureHash'] ?? '';
        unset($vnpData['vnp_SecureHash'], $vnpData['vnp_SecureHashType']);

        ksort($vnpData);
        $queryString = http_build_query($vnpData, '', '&', PHP_QUERY_RFC3986);
        $expected    = hash_hmac('sha512', $queryString, self::HASH_SECRET);

        return hash_equals($expected, $secureHash);
    }

    /**
     * Kiểm tra kết quả thanh toán có thành công không.
     * vnp_ResponseCode = '00' nghĩa là thành công.
     */
    public static function isSuccess(array $vnpData): bool
    {
        return ($vnpData['vnp_ResponseCode'] ?? '') === '00';
    }

    private static function getClientIp(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return explode(',', $_SERVER[$key])[0];
            }
        }
        return '127.0.0.1';
    }
}
