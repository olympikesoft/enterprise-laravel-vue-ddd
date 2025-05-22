<?php

namespace App\Application\Campaign\Handler;

use App\Application\Campaign\Query\ListUserCampaignsQuery;
use App\Domain\Campaign\Repository\CampaignRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUserCampaignsHandler
{
    public function __construct(
        private readonly CampaignRepositoryInterface $campaignRepository
    ) {}

    public function handle(ListUserCampaignsQuery $query): LengthAwarePaginator
    {
        return $this->campaignRepository->findByUserPaginated(
            userId: $query->userId,
            status: $query->status,
            sortBy: $query->sortBy,
            sortDirection: $query->sortDirection,
            perPage: $query->perPage
        );
    }
}
