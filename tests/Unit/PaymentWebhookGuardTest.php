<?php

namespace Tests\Unit;

use App\Services\PaymentWebhookGuard;
use PHPUnit\Framework\TestCase;

class PaymentWebhookGuardTest extends TestCase
{
    public function test_signed_momo_simulation_must_match_payment_method_reference_and_exact_amount(): void
    {
        $data = ['orderId' => 'SVMkt_42_1783900000', 'amount' => '25000'];
        $transaction = ['payment_method' => 'momo', 'amount' => '25000.00', 'payment_status' => 'pending'];

        self::assertTrue(PaymentWebhookGuard::matchesMomoTransaction($data, $transaction, 42));
        self::assertFalse(PaymentWebhookGuard::matchesMomoTransaction($data, $transaction, 41));
        self::assertFalse(PaymentWebhookGuard::matchesMomoTransaction(
            [...$data, 'amount' => '25001'],
            $transaction,
            42
        ));
        self::assertFalse(PaymentWebhookGuard::matchesMomoTransaction(
            $data,
            [...$transaction, 'payment_method' => 'cod'],
            42
        ));
    }

    public function test_zalopay_simulation_is_idempotent_after_payment_and_requires_pending_state(): void
    {
        $data = ['app_trans_id' => '261113_8', 'amount' => 56000];
        $transaction = ['payment_method' => 'zalopay', 'amount' => 56000, 'payment_status' => 'pending'];

        self::assertTrue(PaymentWebhookGuard::matchesZaloPayTransaction($data, $transaction, 8));
        self::assertTrue(PaymentWebhookGuard::shouldMarkPaid($transaction, true));
        self::assertFalse(PaymentWebhookGuard::shouldMarkPaid(
            [...$transaction, 'payment_status' => 'paid'],
            true
        ));
        self::assertFalse(PaymentWebhookGuard::shouldMarkPaid($transaction, false));
    }
}
