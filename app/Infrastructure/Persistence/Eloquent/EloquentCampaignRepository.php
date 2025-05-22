<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Campaign\Aggregate\Campaign as CampaignAggregate;
use App\Domain\Campaign\Repository\CampaignRepositoryInterface;
use App\Domain\Campaign\ValueObject\CampaignStatus;
use App\Domain\Shared\ValueObject\Money;
use App\Infrastructure\Persistence\Models\Campaign as CampaignModel;
use DateTimeImmutable;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentCampaignRepository implements CampaignRepositoryInterface
{
    protected $model;

    public function __construct(CampaignModel $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): ?CampaignAggregate
    {
        $campaignModel = $this->model->find($id);
        if (!$campaignModel) {
            return null;
        }

        return $this->mapToDomainEntity($campaignModel);
    }

    public function findByCreator(int $creatorId): array
    {
        $campaigns = $this->model->where('user_id', $creatorId)->get();
        return $this->mapCollectionToDomainEntities($campaigns);
    }

    public function findActiveCampaigns(): array
    {
        $campaigns = $this->model->where('status', CampaignModel::STATUS_APPROVED)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();
        return $this->mapCollectionToDomainEntities($campaigns);
    }

    public function save(CampaignAggregate $campaign): bool
    {
        $campaignData = $this->mapToModelData($campaign);

        if (isset($campaignData['id'])) {
            $model = $this->model->find($campaignData['id']);
            if (!$model) {
                return false;
            }
            return $model->update($campaignData);
        } else {
            $this->model->create($campaignData);
            return true;
        }
    }

    public function delete(CampaignAggregate $campaign): bool
    {
        $model = $this->model->find($campaign->getId());
        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    public function findByUserPaginated(
        int $userId,
        ?string $status = null,
        ?string $sortBy = null,
        ?string $sortDirection = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = $this->model->where('user_id', $userId);

        // Apply status filter if provided
        if ($status !== null) {
            $query->where('status', $status);
        }

        // Apply sorting if provided
        if ($sortBy !== null && $sortDirection !== null) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            // Default sorting
            $query->orderBy('created_at', 'desc');
        }

        // Return paginated results
        return $query->paginate($perPage);
    }

    /**
     * Implementation of the missing findByPaginate method from the interface
     */
    public function findByPaginate(
        ?string $status = null,
        ?string $sortBy = null,
        ?string $sortDirection = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = $this->model->newQuery();

        // Apply status filter if provided
        if ($status !== null) {
            $query->where('status', $status);
        }

        // Apply sorting if provided
        if ($sortBy !== null && $sortDirection !== null) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            // Default sorting
            $query->orderBy('created_at', 'desc');
        }

        // Return paginated results
        return $query->paginate($perPage);
    }

    public function nextIdentity(): int
    {
        return ($this->model->max('id') ?? 0) + 1;
    }

    /**
     * Helper method to map Eloquent model to Domain entity
     */
    private function mapToDomainEntity(CampaignModel $model): CampaignAggregate
    {
        $campaignId = $model->id;
        $userId = $model->user_id;
        $goalAmount = new Money((int)($model->goal_amount * 100), 'USD');
        $currentAmount = new Money((int)($model->current_amount * 100), 'USD');

        // Convert Carbon dates to DateTimeImmutable
        $startDate = $model->start_date
            ? new DateTimeImmutable($model->start_date->format('Y-m-d H:i:s'))
            : null;
        $endDate = $model->end_date
            ? new DateTimeImmutable($model->end_date->format('Y-m-d H:i:s'))
            : null;

        // Create CampaignStatus value object from string
        $campaignStatus = new CampaignStatus($model->status);

        // Handle approved_at timestamp
        $approvedAt = $model->approved_at
            ? new DateTimeImmutable($model->approved_at->format('Y-m-d H:i:s'))
            : null;

        // Assuming approved_by is an integer field in the model
        $approvedBy = $model->approved_by ?? null;

        return CampaignAggregate::reconstitute(
            $campaignId,
            (int)$userId,
            $model->title,
            $model->description,
            $goalAmount,
            $currentAmount, // Added missing currentAmount parameter
            $startDate,
            $endDate,
            $campaignStatus,
            $approvedAt, // Fixed: Now passing DateTimeImmutable|null for approved_at
            $approvedBy // Fixed: Now passing int|null for approved_by
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
    private function mapToModelData(CampaignAggregate $campaign): array
    {
        return [
            'id' => $campaign->getId(),
            'user_id' => $campaign->getCreatorId(),
            'title' => $campaign->getTitle(),
            'description' => $campaign->getDescription(),
            'status' => $campaign->getStatus(),
            'goal_amount' => $campaign->getGoalAmount()->getAmountInCents() / 100,
            'current_amount' => $campaign->getCurrentAmount(),
            'start_date' => $campaign->getStartDate(),
            'end_date' => $campaign->getEndDate(),
        ];
    }
}
