<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Services\PaymentWebhookProcessor;
use PHPUnit\Framework\TestCase;

class PaymentWebhookProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['MOMO_PARTNER_CODE'] = 'TESTPARTNER';
        $_ENV['MOMO_ACCESS_KEY'] = 'test-access';
        $_ENV['MOMO_SECRET_KEY'] = 'test-secret';
        $_ENV['ZALOPAY_APP_ID'] = '1234';
        $_ENV['ZALOPAY_KEY2'] = 'test-zalo-key';
    }

    public function test_momo_simulation_marks_pending_once_and_duplicate_webhook_is_idempotent(): void
    {
        $pending = ['id' => 42, 'payment_method' => 'momo', 'amount' => '25000.00', 'payment_status' => 'pending', 'product_id' => 5];
        $paid = [...$pending, 'payment_status' => 'paid'];
        $model = $this->createMock(Transaction::class);
        $model->expects(self::exactly(2))->method('findById')->with(42)->willReturnOnConsecutiveCalls($pending, $paid);
        $model->expects(self::once())->method('updatePaymentStatus')->with(42, 'paid');
        $processor = new PaymentWebhookProcessor($model);
        $payload = $this->signedMomoPayload();

        self::assertSame('paid', $processor->processMomo($payload)['status']);
        self::assertSame('duplicate', $processor->processMomo($payload)['status']);
    }

    public function test_momo_bad_signature_fails_before_transaction_lookup(): void
    {
        $model = $this->createMock(Transaction::class);
        $model->expects(self::never())->method('findById');
        $model->expects(self::never())->method('updatePaymentStatus');

        $result = (new PaymentWebhookProcessor($model))->processMomo([
            'orderId' => 'SVMkt_42_1783900000',
            'signature' => 'forged',
        ]);

        self::assertSame('invalid_signature', $result['status']);
    }

    public function test_zalopay_simulation_checks_signed_data_and_ignores_duplicate_callback(): void
    {
        $pending = ['id' => 8, 'payment_method' => 'zalopay', 'amount' => 56000, 'payment_status' => 'pending', 'product_id' => 9];
        $paid = [...$pending, 'payment_status' => 'paid'];
        $model = $this->createMock(Transaction::class);
        $model->expects(self::exactly(2))->method('findById')->with(8)->willReturnOnConsecutiveCalls($pending, $paid);
        $model->expects(self::once())->method('updatePaymentStatus')->with(8, 'paid');
        $processor = new PaymentWebhookProcessor($model);
        $envelope = $this->signedZaloEnvelope();

        self::assertSame('paid', $processor->processZaloPay($envelope)['status']);
        self::assertSame('duplicate', $processor->processZaloPay($envelope)['status']);
    }

    private function signedMomoPayload(): array
    {
        $data = [
            'amount' => '25000', 'extraData' => '', 'message' => 'Success',
            'orderId' => 'SVMkt_42_1783900000', 'orderInfo' => 'Test order',
            'orderType' => 'momo_wallet', 'partnerCode' => 'TESTPARTNER',
            'payType' => 'qr', 'requestId' => 'request-42', 'responseTime' => '1783900000000',
            'resultCode' => '0', 'transId' => 'provider-42',
        ];
        $canonical = 'accessKey=test-access'
            . '&amount=' . $data['amount'] . '&extraData=' . $data['extraData']
            . '&message=' . $data['message'] . '&orderId=' . $data['orderId']
            . '&orderInfo=' . $data['orderInfo'] . '&orderType=' . $data['orderType']
            . '&partnerCode=' . $data['partnerCode'] . '&payType=' . $data['payType']
            . '&requestId=' . $data['requestId'] . '&responseTime=' . $data['responseTime']
            . '&resultCode=' . $data['resultCode'] . '&transId=' . $data['transId'];
        $data['signature'] = hash_hmac('sha256', $canonical, 'test-secret');
        return $data;
    }

    private function signedZaloEnvelope(): array
    {
        $json = json_encode([
            'app_id' => '1234',
            'app_trans_id' => '261113_8',
            'amount' => 56000,
            'status' => 1,
        ], JSON_THROW_ON_ERROR);
        return [
            'data' => $json,
            'mac' => hash_hmac('sha256', $json, 'test-zalo-key'),
        ];
    }
}
