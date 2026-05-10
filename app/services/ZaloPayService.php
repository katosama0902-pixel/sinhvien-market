<?php

namespace App\Services;

class ZaloPayService
{
    private static function getConfig(): array
    {
        // V3-001 fix: bỏ hardcoded fallback để tránh dùng sandbox key trong production
        // Nếu thiếu .env config → API sẽ báo lỗi xác thực (controlled) thay vì dùng sai key
        return [
            'app_id'   => $_ENV['ZALOPAY_APP_ID'] ?? '',
            'key1'     => $_ENV['ZALOPAY_KEY1']   ?? '',
            'key2'     => $_ENV['ZALOPAY_KEY2']   ?? '',
            'endpoint' => 'https://sb-openapi.zalopay.vn/v2/create'
        ];
    }

    /**
     * Tạo order ZaloPay
     */
    public static function createPayment(int $transactionId, int $amount, string $orderInfo): array
    {
        $config = self::getConfig();
        $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
        
        $app_trans_id = date('ymd') . '_' . $transactionId; // Mã giao dịch duy nhất

        $order = [
            'app_id'       => $config['app_id'],
            'app_time'     => round(microtime(true) * 1000), // ms
            'app_trans_id' => $app_trans_id,
            'app_user'     => 'sinhvien_market_user',
            'item'         => json_encode([['id' => $transactionId, 'price' => $amount]]),
            'embed_data'   => json_encode(['redirecturl' => $appUrl . '/transactions/zalopay-return']),
            'amount'       => $amount,
            'description'  => $orderInfo,
            'bank_code'    => 'zalopayapp', // thanh toán qua app ZaloPay
        ];

        // Tạo chữ ký (Mac)
        // app_id|app_trans_id|app_user|amount|app_time|embed_data|item
        $data = $order['app_id'] . '|' . $order['app_trans_id'] . '|' . $order['app_user'] . '|' . $order['amount'] . '|' . $order['app_time'] . '|' . $order['embed_data'] . '|' . $order['item'];
        $order['mac'] = hash_hmac('sha256', $data, $config['key1']);

        // Gửi request tạo order
        $context = stream_context_create([
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($order)
            ]
        ]);

        $result = @file_get_contents($config['endpoint'], false, $context);
        if ($result === false) {
            return ['success' => false, 'message' => 'Không thể kết nối đến ZaloPay.'];
        }

        $resDecode = json_decode($result, true);
        
        if ($resDecode && isset($resDecode['return_code']) && $resDecode['return_code'] == 1) {
            return [
                'success' => true,
                'payUrl'  => $resDecode['order_url']
            ];
        }

        return [
            'success' => false,
            'message' => $resDecode['return_message'] ?? 'Lỗi không xác định từ ZaloPay.'
        ];
    }

    /**
     * Xác thực thông tin từ ZaloPay redirect về
     */
    public static function verifyReturnSignature(array $data): bool
    {
        $config = self::getConfig();
        $checksumData = $data['appid'] . '|' . $data['apptransid'] . '|' . $data['pmcid'] . '|' . $data['bankcode'] . '|' . $data['amount'] . '|' . $data['discountamount'] . '|' . $data['status'];
        $mac = hash_hmac('sha256', $checksumData, $config['key2']);
        
        return hash_equals($mac, $data['mac']);
    }

    /**
     * Xác thực thông tin từ ZaloPay Server gọi về (IPN/Callback)
     */
    public static function verifyCallbackSignature(string $dataStr, string $reqMac): bool
    {
        $config = self::getConfig();
        $mac = hash_hmac('sha256', $dataStr, $config['key2']);
        return hash_equals($mac, $reqMac);
    }
}
