<?php

namespace App\Libraries\Payments\Providers;

use App\Libraries\Payments\PaymentProviderBase;

class SumUpProvider extends PaymentProviderBase
{
    public function getProviderId(): string
    {
        return 'sumup';
    }

    public function getProviderName(): string
    {
        return 'SumUp';
    }

    public function getProviderDescription(): string
    {
        return 'Accept card payments using SumUp card reader terminals.';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getPaymentTypes(): array
    {
        return [
            'sumup_card' => lang('Sales.sumup_card') ?? 'Card (SumUp)',
        ];
    }

    public function getIcon(?string $paymentType = null): ?string
    {
        return base_url('images/payment_providers/sumup.svg');
    }

    public function initiatePayment(float $amount, string $currency = 'USD', array $options = []): array
    {
        $checkoutReference = $options['checkout_reference'] ?? uniqid('sumup_', true);
        
        $this->logTransaction(
            $checkoutReference,
            'pending',
            $amount,
            $currency,
            $options['sale_id'] ?? null,
            ['options' => $options]
        );

        return [
            'success' => true,
            'transaction_id' => $checkoutReference,
            'status' => 'pending',
            'checkout_reference' => $checkoutReference,
            'amount' => $amount,
            'currency' => $currency,
            'message' => 'Payment initiated. Use SumUp terminal to complete.',
        ];
    }

    public function processCallback(array $data): array
    {
        $eventType = $data['event_type'] ?? '';
        $checkoutId = $data['checkout_id'] ?? $data['id'] ?? null;

        if (!$checkoutId) {
            return ['success' => false, 'error' => 'Missing checkout ID'];
        }

        $transaction = $this->getTransaction($checkoutId);
        if (!$transaction) {
            return ['success' => false, 'error' => 'Transaction not found'];
        }

        switch ($eventType) {
            case 'payment.success':
                $this->updateTransactionStatus($checkoutId, 'completed', $data);
                $this->fire('completed', [
                    'transaction_id' => $checkoutId,
                    'sale_id' => $transaction['sale_id'],
                    'amount' => $transaction['amount'],
                ]);
                return ['success' => true, 'status' => 'completed'];

            case 'payment.failed':
                $this->updateTransactionStatus($checkoutId, 'failed', $data);
                $this->fire('failed', [
                    'transaction_id' => $checkoutId,
                    'error' => $data['error_message'] ?? 'Unknown error',
                ]);
                return ['success' => false, 'status' => 'failed', 'error' => $data['error_message'] ?? 'Payment failed'];

            default:
                return ['success' => false, 'error' => "Unknown event type: {$eventType}"];
        }
    }

    public function getPaymentStatus(string $transactionId): array
    {
        $transaction = $this->getTransaction($transactionId);
        
        if (!$transaction) {
            return ['success' => false, 'error' => 'Transaction not found'];
        }

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'status' => $transaction['status'],
            'amount' => (float)$transaction['amount'],
            'currency' => $transaction['currency'],
        ];
    }

    public function refund(string $transactionId, float $amount, string $reason = ''): array
    {
        $transaction = $this->getTransaction($transactionId);
        
        if (!$transaction) {
            return ['success' => false, 'error' => 'Transaction not found'];
        }

        if ($transaction['status'] !== 'completed') {
            return ['success' => false, 'error' => 'Transaction cannot be refunded'];
        }

        $this->updateTransactionStatus($transactionId, 'refunded', [
            'refund_amount' => $amount,
            'refund_reason' => $reason,
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'status' => 'refunded',
            'refund_amount' => $amount,
        ];
    }

    public function cancel(string $transactionId): array
    {
        $transaction = $this->getTransaction($transactionId);
        
        if (!$transaction) {
            return ['success' => false, 'error' => 'Transaction not found'];
        }

        if ($transaction['status'] !== 'pending') {
            return ['success' => false, 'error' => 'Transaction cannot be cancelled'];
        }

        $this->updateTransactionStatus($transactionId, 'cancelled');

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'status' => 'cancelled',
        ];
    }

    public function getConfigView(): ?string
    {
        return 'Payments/sumup_config';
    }
}