<?php

namespace App\Libraries\Payments\Providers;

use App\Libraries\Payments\PaymentProviderBase;

class PayPalProvider extends PaymentProviderBase
{
    public function getProviderId(): string
    {
        return 'paypal';
    }

    public function getProviderName(): string
    {
        return 'PayPal';
    }

    public function getProviderDescription(): string
    {
        return 'Accept payments using PayPal (Zettle) card reader terminals and QR code payments.';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getPaymentTypes(): array
    {
        return [
            'paypal_card' => lang('Sales.paypal_card') ?? 'Card (PayPal/Zettle)',
            'paypal_qr' => lang('Sales.paypal_qr') ?? 'PayPal QR',
        ];
    }

    public function getIcon(?string $paymentType = null): ?string
    {
        return base_url('images/payment_providers/paypal.svg');
    }

    public function initiatePayment(float $amount, string $currency = 'USD', array $options = []): array
    {
        $orderId = $options['order_id'] ?? uniqid('paypal_', true);
        $paymentType = $options['payment_type'] ?? 'paypal_card';

        $this->logTransaction(
            $orderId,
            'pending',
            $amount,
            $currency,
            $options['sale_id'] ?? null,
            ['payment_type' => $paymentType, 'options' => $options]
        );

        return [
            'success' => true,
            'transaction_id' => $orderId,
            'status' => 'pending',
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => $currency,
            'payment_type' => $paymentType,
            'message' => 'Payment initiated. ' . 
                ($paymentType === 'paypal_qr' 
                    ? 'Customer can scan QR code to complete payment.' 
                    : 'Use PayPal Zettle terminal to complete.'),
        ];
    }

    public function processCallback(array $data): array
    {
        $eventType = $data['event_type'] ?? '';
        $orderId = $data['resource']['id'] ?? $data['order_id'] ?? null;

        if (!$orderId) {
            return ['success' => false, 'error' => 'Missing order ID'];
        }

        $transaction = $this->getTransaction($orderId);
        if (!$transaction) {
            return ['success' => false, 'error' => 'Transaction not found'];
        }

        switch ($eventType) {
            case 'CHECKOUT.ORDER.APPROVED':
            case 'PAYMENT.CAPTURE.COMPLETED':
                $this->updateTransactionStatus($orderId, 'completed', $data);
                $this->fire('completed', [
                    'transaction_id' => $orderId,
                    'sale_id' => $transaction['sale_id'],
                    'amount' => $transaction['amount'],
                ]);
                return ['success' => true, 'status' => 'completed'];

            case 'PAYMENT.CAPTURE.DENIED':
            case 'PAYMENT.CAPTURE.DECLINED':
                $this->updateTransactionStatus($orderId, 'failed', $data);
                $this->fire('failed', [
                    'transaction_id' => $orderId,
                    'error' => $data['resource']['status_details'] ?? 'Payment declined',
                ]);
                return ['success' => false, 'status' => 'failed', 'error' => 'Payment declined'];

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
        return 'Payments/paypal_config';
    }
}