<?php

namespace Tests\Unit;

use App\Services\MomoService;
use App\Services\ZaloPayService;
use PHPUnit\Framework\TestCase;

class PaymentSignatureTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['MOMO_PARTNER_CODE'] = 'TESTPARTNER';
        $_ENV['MOMO_ACCESS_KEY'] = 'test-access';
        $_ENV['MOMO_SECRET_KEY'] = 'test-secret';
        $_ENV['ZALOPAY_APP_ID'] = '1234';
        $_ENV['ZALOPAY_KEY2'] = 'test-zalo-key';
    }

    public function test_momo_signature_accepts_valid_simulation_and_rejects_tampering_or_missing_signature(): void
    {
        $data = [
            'amount' => '25000', 'extraData' => '', 'message' => 'Success',
            'orderId' => 'SVMkt_7_1783900000', 'orderInfo' => 'Test order',
            'orderType' => 'momo_wallet', 'partnerCode' => 'TESTPARTNER',
            'payType' => 'qr', 'requestId' => 'request-7', 'responseTime' => '1783900000000',
            'resultCode' => '0', 'transId' => 'provider-7',
        ];
        $canonical = 'accessKey=test-access'
            . '&amount=' . $data['amount'] . '&extraData=' . $data['extraData']
            . '&message=' . $data['message'] . '&orderId=' . $data['orderId']
            . '&orderInfo=' . $data['orderInfo'] . '&orderType=' . $data['orderType']
            . '&partnerCode=' . $data['partnerCode'] . '&payType=' . $data['payType']
            . '&requestId=' . $data['requestId'] . '&responseTime=' . $data['responseTime']
            . '&resultCode=' . $data['resultCode'] . '&transId=' . $data['transId'];
        $data['signature'] = hash_hmac('sha256', $canonical, 'test-secret');

        self::assertTrue(MomoService::verifySignature($data));
        self::assertFalse(MomoService::verifySignature([...$data, 'amount' => '25001']));
        unset($data['signature']);
        self::assertFalse(MomoService::verifySignature($data));
    }

    public function test_zalopay_callback_signature_fails_closed_when_secret_is_missing(): void
    {
        $signed = 'test-callback';
        $mac = hash_hmac('sha256', $signed, 'test-zalo-key');
        self::assertTrue(ZaloPayService::verifyCallbackSignature($signed, $mac));
        self::assertFalse(ZaloPayService::verifyCallbackSignature($signed, ''));

        $_ENV['ZALOPAY_KEY2'] = '';
        self::assertFalse(ZaloPayService::verifyCallbackSignature($signed, $mac));
    }
}
