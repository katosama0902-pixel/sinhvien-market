<?php

namespace App\Services;

use App\Models\Transaction;

/**
 * Verifies simulated/provider webhook payloads and idempotently updates a transaction.
 */
class PaymentWebhookProcessor
{
    public function __construct(private readonly Transaction $transactions = new Transaction())
    {
    }

    /**
     * @return array{status:string,transaction:?array}
     */
    public function processMomo(array $payload): array
    {
        if (!MomoService::verifySignature($payload)) {
            return ['status' => 'invalid_signature', 'transaction' => null];
        }
        if (!preg_match('/^SVMkt_(\d+)_\d+$/', (string)($payload['orderId'] ?? ''), $matches)) {
            return ['status' => 'invalid_reference', 'transaction' => null];
        }
        $id = (int)$matches[1];
        $transaction = $this->transactions->findById($id);
        if ($transaction === null) {
            return ['status' => 'transaction_missing', 'transaction' => null];
        }
        if (!PaymentWebhookGuard::matchesMomoTransaction($payload, $transaction, $id)) {
            return ['status' => 'transaction_mismatch', 'transaction' => $transaction];
        }
        if (($transaction['payment_status'] ?? '') === 'paid') {
            return ['status' => 'duplicate', 'transaction' => $transaction];
        }
        if (!PaymentWebhookGuard::shouldMarkPaid($transaction, MomoService::isSuccess($payload))) {
            return ['status' => 'not_successful', 'transaction' => $transaction];
        }

        $this->transactions->updatePaymentStatus($id, 'paid');
        return ['status' => 'paid', 'transaction' => $transaction];
    }

    /**
     * @return array{status:string,transaction:?array}
     */
    public function processZaloPay(array $envelope): array
    {
        $dataString = is_string($envelope['data'] ?? null) ? $envelope['data'] : '';
        $mac = is_string($envelope['mac'] ?? null) ? $envelope['mac'] : '';
        if ($dataString === '' || !ZaloPayService::verifyCallbackSignature($dataString, $mac)) {
            return ['status' => 'invalid_signature', 'transaction' => null];
        }
        $payload = json_decode($dataString, true);
        $appId = (string)($_ENV['ZALOPAY_APP_ID'] ?? '');
        if (!is_array($payload) || $appId === '' || (string)($payload['app_id'] ?? '') !== $appId
            || !preg_match('/^\d{6}_(\d+)$/', (string)($payload['app_trans_id'] ?? ''), $matches)) {
            return ['status' => 'invalid_reference', 'transaction' => null];
        }
        $id = (int)$matches[1];
        $transaction = $this->transactions->findById($id);
        if ($transaction === null) {
            return ['status' => 'transaction_missing', 'transaction' => null];
        }
        if (!PaymentWebhookGuard::matchesZaloPayTransaction($payload, $transaction, $id)) {
            return ['status' => 'transaction_mismatch', 'transaction' => $transaction];
        }
        if (($transaction['payment_status'] ?? '') === 'paid') {
            return ['status' => 'duplicate', 'transaction' => $transaction];
        }
        if (!PaymentWebhookGuard::shouldMarkPaid($transaction, (int)($payload['status'] ?? 0) === 1)) {
            return ['status' => 'not_successful', 'transaction' => $transaction];
        }

        $this->transactions->updatePaymentStatus($id, 'paid');
        return ['status' => 'paid', 'transaction' => $transaction];
    }
}
