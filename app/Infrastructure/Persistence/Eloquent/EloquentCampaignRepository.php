<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Campaign\Aggregate\Campaign as CampaignAggregate;
use App\Domain\Campaign\Repository\CampaignRepositoryInterface;
use App\Infrastructure\Persistence\Models\Campaign as CampaignModel;

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
        $campaigns = $this->model->where('creator_id', $creatorId)->get();
        return $this->mapCollectionToDomainEntities($campaigns);
    }

    public function findActiveCampaigns(): array
    {
        $campaigns = $this->model->where('is_active', true)->get();
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

    public function nextIdentity(): int
    {
        return ($this->model->max('id') ?? 0) + 1;
    }

    /**
     * Helper method to map Eloquent model to Domain entity
     */
    private function mapToDomainEntity(CampaignModel $model): CampaignAggregate
    {
        return new CampaignAggregate(
            $model->id,
            $model->creator_id,
            $model->title,
            $model->description,
            $model->status,
            $model->start_date,
            $model->end_date
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
            'creator_id' => $campaign->getCreatorId(),
            'title' => $campaign->getTitle(),
            'description' => $campaign->getDescription(),
            'is_active' => $campaign->getStatus(),
        ];
    }
}
