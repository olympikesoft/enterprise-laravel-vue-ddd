<?php

namespace App\Application\DTO\Donation;

class MakeDonationDTO
{
    public function __construct(
        public readonly int $campaignId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly int $userId,
        public readonly ?string $donorName,
        public readonly ?string $message,
        public readonly string $paymentToken
    ) {}
}