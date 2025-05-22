<?php

namespace App\Application\Campaign\Handler;

use App\Application\Campaign\Query\ListUserCampaignsQuery;
use App\Domain\Campaign\Repository\CampaignRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUserCampaignsHandler
{
    public function __construct(
        private readonly CampaignRepositoryInterface $CampaignRepository
    ) {}

    public function handle(ListUserCampaignsQuery $query): LengthAwarePaginator
    {
        return $this->CampaignRepository->findByUserPaginated(
            userId: $query->userId,
            sortBy: $query->sortBy,
            sortDirection: $query->sortDirection,
            perPage: $query->perPage,
            status: $query->status,
        );
    }
}
