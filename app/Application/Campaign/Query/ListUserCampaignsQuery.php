<?php

namespace App\Application\Campaign\Query;

class ListUserCampaignsQuery
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $status,
        public readonly string $sortBy,
        public readonly string $sortDirection,
        public readonly int $perPage
    ) {}
}
