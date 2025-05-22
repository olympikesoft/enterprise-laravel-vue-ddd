<?php

namespace App\Application\Campaign\Handler;

use App\Application\Campaign\Query\ListCampaignsQuery;
use App\Domain\Campaign\Repository\CampaignRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCampaignsHandler
{
    public function __construct(
        private readonly CampaignRepositoryInterface $CampaignRepository
    ) {}

    public function handle(ListCampaignsQuery $query): LengthAwarePaginator
    {
        return $this->CampaignRepository->findByPaginate(
            sortBy: $query->sortBy,
            sortDirection: $query->sortDirection,
            perPage: $query->perPage,
            status: $query->status,
        );
    }
}
