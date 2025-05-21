<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Campaign\ValueObject\CampaignId;
use App\Domain\Donation\Aggregate\Donation;
use App\Domain\Donation\Repository\DonationRepositoryInterface;
use App\Domain\Donation\ValueObject\DonationId;
use App\Infrastructure\Persistence\Models\Donation as DonationModel;
use Illuminate\Support\Str;

class EloquentDonationRepository implements DonationRepositoryInterface
{
    protected $model;

    public function __construct(DonationModel $model)
    {
        $this->model = $model;
    }

    /**
     * Find a donation by its ID.
     *
     * @param DonationId $id
     * @return Donation|null
     */
    public function findById(DonationId $id): ?Donation
    {
        $donation = $this->model->find($id->toString());
        if (!$donation) {
            return null;
        }

        return $this->mapToDomainEntity($donation);
    }

    /**
     * Save a donation instance.
     *
     * @param Donation $donation
     * @return void
     */
    public function save(Donation $donation): void
    {
        $donationData = $this->mapToModelData($donation);

        if (isset($donationData['id'])) {
            $model = $this->model->find($donationData['id']);
            if ($model) {
                $model->update($donationData);
            } else {
                $this->model->create($donationData);
            }
        } else {
            $this->model->create($donationData);
        }
    }

    /**
     * Generate the next unique identity for a donation.
     *
     * @return DonationId
     */
    public function nextIdentity(): DonationId
    {
        return DonationId::fromString(Str::uuid()->toString());
    }

    /**
     * Find all donations for a campaign.
     *
     * @param CampaignId $campaignId
     * @return Donation[]
     */
    public function findByCampaignId(CampaignId $campaignId): array
    {
        $donations = $this->model->where('campaign_id', $campaignId->toString())->get();
        return $this->mapCollectionToDomainEntities($donations);
    }

    /**
     * Helper method to map Eloquent model to Domain entity
     */
    private function mapToDomainEntity(DonationModel $model): Donation
    {
        // Implementation would depend on your domain entity constructor
        // This is a simplified example
        return Donation::reconstituteFromArray([
            'id' => DonationId::fromString($model->id),
            'amount' => $model->amount,
            'donorId' => $model->donor_id, // Assuming this is already a value object or will be converted
            'campaignId' => CampaignId::fromString($model->campaign_id),
            // Add other properties as needed
        ]);
    }

    /**
     * Helper method to map collection of models to domain entities
     */
    private function mapCollectionToDomainEntities($models): array
    {
        return $models->map(function ($model) {
            return $this->mapToDomainEntity($model);
        })->toArray();
    }

    /**
     * Helper method to map Domain entity to Model data
     */
    private function mapToModelData(Donation $donation): array
    {
        // Implementation would depend on your domain entity getters
        // This is a simplified example
        return [
            'id' => $donation->getId()->toString(),
            'amount' => $donation->getAmount(),
            'donor_id' => $donation->getDonorId()->toString(), // Assuming this returns a value object
            'campaign_id' => $donation->getCampaignId()->toString(),
            // Add other properties as needed
        ];
    }
}
