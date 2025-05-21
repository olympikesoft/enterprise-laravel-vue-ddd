<?php

namespace App\Application\User\Query;

class ListUsersQuery
{
    public function __construct(
        public readonly ?string $roleFilter,
        public readonly ?string $status, // e.g., 'active', 'inactive', 'banned'
        public readonly ?string $search,
        public readonly string $sortBy,
        public readonly string $sortDirection,
        public readonly int $perPage
    ) {}
}
