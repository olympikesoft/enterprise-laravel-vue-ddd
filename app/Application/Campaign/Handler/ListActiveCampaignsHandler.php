<?php

namespace App\Application\Campaign\Handler;

use App\Infrastructure\Persistence\Models\Campaign;
use Illuminate\Contracts\Pagination\LengthAwarePaginator; // For pagination
use Illuminate\Database\Eloquent\Collection;

// This could also be a Query Handler if you have a specific Query object
class ListActiveCampaignsHandler
{
    /**
     * Fetches active and approved campaigns.
     *
     * @param int $perPage Number of items per page for pagination.
     * @param string|null $sortBy Field to sort by.
     * @param string $sortDirection 'asc' or 'desc'.
     * @return LengthAwarePaginator|Collection
     */
    public function handle(int $perPage = 15, ?string $sortBy = 'created_at', string $sortDirection = 'desc'): LengthAwarePaginator|Collection
    {
        $query = Campaign::where('status', Campaign::STATUS_APPROVED) // Or STATUS_ACTIVE if you have that
                         ->where('start_date', '<=', now())
                         ->where('end_date', '>=', now());

        if ($sortBy) {
            $query->orderBy($sortBy, $sortDirection);
        }

        if ($perPage > 0) {
            return $query->paginate($perPage);
        }
        return $query->get();
    }
}