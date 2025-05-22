<?php

namespace App\Application\Campaign\Query;

class ListCampaignsQuery
{
    public function __construct(
        public readonly ?string $status,
        public readonly string $sortBy,
        public readonly string $sortDirection,
        public readonly int $perPage
    ) {}
}
