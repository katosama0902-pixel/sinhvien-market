<?php

namespace App\Services;

class VietQRService
{
    /**
     * Tạo URL ảnh mã QR theo chuẩn VietQR.
     * Sử dụng Public API của vietqr.io (Hoàn toàn miễn phí, không cần API key).
     * 
     * @param string $bankCode   Mã BIN ngân hàng (VD: 'VCB' hoặc số BIN)
     * @param string $accountNo  Số tài khoản
     * @param int    $amount     Số tiền cần chuyển
     * @param string $description Nội dung chuyển khoản (VD: DH-123)
     * @param string $accountName Tên chủ tài khoản
     * @return string URL ảnh QR
     */
    public static function generateQRUrl(
        string $bankCode,
        string $accountNo,
        int $amount,
        string $description,
        string $accountName
    ): string {
        $baseUrl = 'https://img.vietqr.io/image';
        
        // Template VietQR format: {bankId}-{accountNo}-compact2.png
        $path = sprintf('%s-%s-compact2.png', urlencode($bankCode), urlencode($accountNo));
        
        $params = http_build_query([
            'amount'      => $amount,
            'addInfo'     => $description,
            'accountName' => $accountName
        ]);
        
        return sprintf('%s/%s?%s', $baseUrl, $path, $params);
    }
}
