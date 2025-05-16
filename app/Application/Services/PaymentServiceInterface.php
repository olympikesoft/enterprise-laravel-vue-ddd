<?php

namespace App\Application\Services;

/**
 * Interface for processing payments.
 */
interface PaymentServiceInterface
{
    /**
     * Processes a payment.
     *
     * @param float $amount The amount to charge.
     * @param string $currency The currency code (e.g., "USD").
     * @param string $paymentToken A token representing the payment method (e.g., Stripe token).
     * @param string $description A description for the payment.
     * @param array $metadata Additional metadata for the payment.
     * @return array{success: bool, transactionId: ?string, message: ?string, gatewayResponse: mixed}
     */
    public function processPayment(
        float $amount,
        string $currency,
        string $paymentToken,
        string $description,
        array $metadata = []
    ): array;
}