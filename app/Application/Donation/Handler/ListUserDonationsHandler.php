<?php

namespace App\Application\Donation\Handler;

use App\Application\Donation\Query\ListUserDonationsQuery;
use App\Domain\Donation\Repository\DonationRepositoryInterface; // You'll need this
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUserDonationsHandler
{
    public function __construct(
        private readonly DonationRepositoryInterface $donationRepository
    ) {}

    public function handle(ListUserDonationsQuery $query): LengthAwarePaginator
    {
        return $this->donationRepository->findByUserPaginated(
            userId: $query->userId,
            sortBy: $query->sortBy,
            sortDirection: $query->sortDirection,
            perPage: $query->perPage,
            paymentStatus: $query->paymentStatus
        );
    }
}
