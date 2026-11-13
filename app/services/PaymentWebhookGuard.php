<?php

namespace App\Services;

/**
 * Pure payment callback checks shared by browser returns and server webhooks.
 * Signature verification remains provider-specific and must happen first.
 */
class PaymentWebhookGuard
{
    public static function matchesMomoTransaction(array $data, array $transaction, int $transactionId): bool
    {
        return ($transaction['payment_method'] ?? '') === 'momo'
            && preg_match('/^SVMkt_' . preg_quote((string)$transactionId, '/') . '_\d+$/', (string)($data['orderId'] ?? '')) === 1
            && self::amountMatches($data['amount'] ?? null, $transaction['amount'] ?? null);
    }

    public static function matchesZaloPayTransaction(array $data, array $transaction, int $transactionId): bool
    {
        return ($transaction['payment_method'] ?? '') === 'zalopay'
            && preg_match('/^\d{6}_' . preg_quote((string)$transactionId, '/') . '$/', (string)($data['app_trans_id'] ?? '')) === 1
            && self::amountMatches($data['amount'] ?? null, $transaction['amount'] ?? null);
    }

    public static function shouldMarkPaid(array $transaction, bool $providerReportsSuccess): bool
    {
        return $providerReportsSuccess
            && ($transaction['payment_status'] ?? '') === 'pending';
    }

    private static function amountMatches(mixed $received, mixed $expected): bool
    {
        if (!is_scalar($received) || !is_numeric($received) || !is_numeric($expected)) {
            return false;
        }
        return (string)(int)$received === (string)(int)$expected
            && (float)$received === (float)$expected;
    }
}
