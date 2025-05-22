<?php

namespace App\Infrastructure\Payment;

use App\Application\Services\PaymentServiceInterface;
use Illuminate\Support\Str;

class MockPaymentService implements PaymentServiceInterface
{
    /**
     * Mock payment processing (for testing/development)
     *
     * @param float $amount
     * @param string $currency
     * @param string $description
     * @param array $metadata
     * @return array
     */
    public function processPayment(
        float $amount,
        string $currency,
        string $description,
        array $metadata = []
    ): array {
        // Simulate some processing time
        usleep(500000); // 0.5 seconds

        // Generate a mock transaction ID
        $transactionId = 'mock_' . Str::random(20);

        // Simulate a success rate (90% success for testing)
        $success = rand(1, 100) <= 90;

        if ($success) {
            return [
                'success' => true,
                'transactionId' => $transactionId,
                'clientSecret' => 'mock_client_secret_' . Str::random(10),
                'status' => 'succeeded',
                'amount' => $amount,
                'currency' => $currency,
                'gatewayResponse' => [
                    'id' => $transactionId,
                    'amount' => $amount * 100, // Mock cents conversion
                    'currency' => strtolower($currency),
                    'status' => 'succeeded',
                    'description' => $description,
                    'metadata' => $metadata,
                    'created' => now()->timestamp,
                ],
                'message' => 'Mock payment processed successfully'
            ];
        } else {
            return [
                'success' => false,
                'transactionId' => null,
                'clientSecret' => null,
                'status' => 'failed',
                'amount' => $amount,
                'currency' => $currency,
                'gatewayResponse' => [
                    'error' => 'Mock payment failure',
                    'error_code' => 'card_declined',
                    'error_message' => 'Your card was declined.'
                ],
                'message' => 'Mock payment failed'
            ];
        }
    }

    /**
     * Mock refund processing
     *
     * @param string $transactionId
     * @param float|null $amount
     * @return array
     */
    public function refundPayment(string $transactionId, ?float $amount = null): array
    {
        // Simulate processing time
        usleep(300000); // 0.3 seconds

        $refundId = 'mock_refund_' . Str::random(20);

        return [
            'success' => true,
            'refundId' => $refundId,
            'status' => 'succeeded',
            'amount' => $amount ?? 100.00, // Mock amount if not provided
            'gatewayResponse' => [
                'id' => $refundId,
                'payment_intent' => $transactionId,
                'amount' => ($amount ?? 100.00) * 100,
                'status' => 'succeeded',
                'created' => now()->timestamp,
            ],
            'message' => 'Mock refund processed successfully'
        ];
    }

    /**
     * Mock payment status retrieval
     *
     * @param string $transactionId
     * @return array
     */
    public function getPaymentStatus(string $transactionId): array
    {
        // Simulate processing time
        usleep(200000); // 0.2 seconds

        return [
            'success' => true,
            'transactionId' => $transactionId,
            'status' => 'succeeded',
            'amount' => 100.00, // Mock amount
            'currency' => 'USD',
            'gatewayResponse' => [
                'id' => $transactionId,
                'amount' => 10000, // Mock cents
                'currency' => 'usd',
                'status' => 'succeeded',
                'created' => now()->subMinutes(5)->timestamp,
            ],
            'message' => 'Mock payment status retrieved successfully'
        ];
    }
}
