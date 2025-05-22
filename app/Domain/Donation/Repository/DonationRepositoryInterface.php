<?php

declare(strict_types=1);

namespace App\Domain\Donation\Repository;

use App\Domain\Donation\Aggregate\Donation;
use App\Domain\Donation\ValueObject\DonationId;

interface DonationRepositoryInterface
{
    /**
     * Find a donation by its ID.
     *
     * @param DonationId $id
     * @return Donation|null
     */
    public function findById(DonationId $id): ?Donation;

    /**
     * Save a donation.
     *
     * @param Donation $donation
     * @return void
     */
    public function save(Donation $donation): void;

    /**
     * Generate a new donation ID.
     *
     * @return DonationId
     */
    public function nextIdentity(): DonationId;

    /**
     * Find all donations for a campaign.
     *
     * @param int $campaignId
     * @return Donation[]
     */
    public function findByCampaignId(int $campaignId): array;


    public function hasCompletedDonationsForCampaign(int $campaignId): bool;

    public function findByUserPaginated(
        int $userId,
        string $sortBy,
        string $sortDirection,
        int $perPage,
        ?string $paymentStatus = null
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}
