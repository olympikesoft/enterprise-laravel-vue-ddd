<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Donation\Aggregate\Donation;
use App\Domain\Donation\Repository\DonationRepositoryInterface;
use App\Domain\Donation\ValueObject\DonationId;
use App\Domain\Donation\ValueObject\DonationStatus;
use App\Domain\Shared\ValueObject\Money;
use App\Infrastructure\Persistence\Models\Donation as DonationModel;
use DateTimeImmutable;
use Illuminate\Support\Str;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
     * @param int $campaignId
     * @return Donation[]
     */
    public function findByCampaignId(int $campaignId): array
    {
        $donations = $this->model->where('campaign_id', $campaignId)->get();
        return $this->mapCollectionToDomainEntities($donations);
    }

    /**
     * Check if a user has completed donations for a campaign.
     *
     * @param int $campaignId
     * @return bool
     */
    public function hasCompletedDonationsForCampaign(int $campaignId): bool
    {
        return $this->model->where('campaign_id', $campaignId)
            ->where('status', DonationModel::PAYMENT_STATUS_COMPLETED)
            ->exists();
    }

    /**
     * Find donations by user with pagination.
     *
     * @param int $userId
     * @param string $sortBy
     * @param string $sortDirection
     * @param int $perPage
     * @param string|null $paymentStatus
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function findByUserPaginated(
        int $userId,
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = 15,
        ?string $paymentStatus = null
    ): LengthAwarePaginator {
        $query = $this->model->where('user_id', $userId);

        // Apply payment status filter if provided
        if ($paymentStatus !== null) {
            $query->where('status', $paymentStatus);
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortDirection);

        // Return paginated results
        return $query->paginate($perPage);
    }

    /**
     * Helper method to map Eloquent model to Domain entity
     */
    private function mapToDomainEntity(DonationModel $model): Donation
    {
        // Convert amount to Money value object (with USD currency code)
        $amount = new Money((int)($model->amount * 100), 'USD'); // Convert to cents and provide currency

        // Convert string status to DonationStatus value object
        $status = match($model->status) {
            DonationModel::PAYMENT_STATUS_COMPLETED => DonationStatus::succeeded(),
            DonationModel::PAYMENT_STATUS_FAILED => DonationStatus::failed(),
            default => DonationStatus::pending(),
        };

        // Map from persistence model to domain entity using reconstitute method
        return Donation::reconstitute(
            $model->id,
            $model->campaign_id,
            $model->user_id,
            $amount,
            $status,
            $model->notes ?? null,
            $model->created_at ? new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')) : new DateTimeImmutable(),
            $model->transaction_id ?? null,
            $model->payment_gateway_response ?? null
        );
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
        // Extract values from value objects
        $donationId = $donation->getId();
        $idValue = $donationId;

        $campaignId = $donation->getCampaignId();
        $campaignIdValue = $campaignId;

        $donorId = $donation->getDonorId();
        $donorIdValue = $donorId;

        $status = $donation->getStatus();
        $statusValue = match(true) {
            $status->equals(DonationStatus::succeeded()) => DonationModel::PAYMENT_STATUS_COMPLETED,
            $status->equals(DonationStatus::failed()) => DonationModel::PAYMENT_STATUS_FAILED,
            default => DonationModel::PAYMENT_STATUS_PENDING,
        };

        return [
            'id' => $idValue,
            'campaign_id' => $campaignIdValue,
            'user_id' => $donorIdValue,
            'amount' => $donation->getAmount()->getAmountInCents() / 100,
            'notes' => $donation->getMessage(),
            'status' => $statusValue,
            'transaction_id' => $donation->getTransactionReference(),
            'payment_gateway_response' => $donation->getFailureReason(),
        ];
    }
}
