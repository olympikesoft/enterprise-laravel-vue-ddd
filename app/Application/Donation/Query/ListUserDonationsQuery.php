<?php

namespace App\Application\Donation\Query;

class ListUserDonationsQuery
{
    public function __construct(
        public readonly int $userId,
        public readonly string $sortBy,
        public readonly string $sortDirection,
        public readonly int $perPage,
        public readonly ?string $paymentStatus = null // Optional filter
    ) {}
}
