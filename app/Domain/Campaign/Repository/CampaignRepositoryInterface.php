<?php

declare(strict_types=1);

namespace App\Domain\Campaign\Repository;

use App\Domain\Campaign\Aggregate\Campaign;
use Illuminate\Pagination\LengthAwarePaginator;

interface CampaignRepositoryInterface
{
    public function findById(int $id): ?Campaign;
    public function save(Campaign $campaign): bool;
    public function delete(Campaign $campaign): bool;
    public function nextIdentity(): int; // Useful for generating IDs before saving

    /**
     * @return Campaign[]
     */
    public function findByCreator(int $creatorId): array;

    /**
     * @return Campaign[]
     */
    public function findActiveCampaigns(): array;

    public function findByPaginate(
        ?string $status = null,
        ?string $sortBy = null,
        ?string $sortDirection = null,
        int $perPage = 10
    ): LengthAwarePaginator;

    public function findByUserPaginated(
        int $userId,
        ?string $status = null,
        ?string $sortBy = null,
        ?string $sortDirection = null,
        int $perPage = 10
    ): LengthAwarePaginator;
}
