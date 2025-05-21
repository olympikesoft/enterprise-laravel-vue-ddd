<?php

namespace App\Infrastructure\Payment;

use Exception;

namespace App\Infrastructure\Payment;


class PaymentService
{
    protected $paymentProvider;

    public function __construct($paymentProvider)
    {
        $this->paymentProvider = $paymentProvider;
    }

    /**
     * Process a payment.
     *
     * @param float $amount
     * @param string $currency
     * @param array $paymentDetails
     * @return array
     * @throws Exception
     */
    public function processPayment(float $amount, string $currency, array $paymentDetails): array
    {
        try {
            return $this->paymentProvider->charge($amount, $currency, $paymentDetails);
        } catch (Exception $e) {
            throw new \Exception("Payment processing failed: " . $e->getMessage());
        }
    }

    /**
     * Refund a payment.
     *
     * @param string $transactionId
     * @param float|null $amount
     * @return array
     * @throws Exception
     */
    public function refundPayment(string $transactionId, ?float $amount = null): array
    {
        try {
            return $this->paymentProvider->refund($transactionId, $amount);
        } catch (Exception $e) {
            throw new Exception("Refund processing failed: " . $e->getMessage());
        }
    }

    /**
     * Retrieve payment details.
     *
     * @param string $transactionId
     * @return array
     * @throws Exception
     */
    public function getPaymentDetails(string $transactionId): array
    {
        try {
            return $this->paymentProvider->retrieve($transactionId);
        } catch (Exception $e) {
            throw new Exception("Failed to retrieve payment details: " . $e->getMessage());
        }
    }
}
