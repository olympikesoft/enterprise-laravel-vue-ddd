<?php

namespace App\Application\Campaign\Handler;

use App\Infrastructure\Persistence\Models\Campaign;
use App\Infrastructure\Persistence\Models\Donation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

// This could also be a Query Handler (e.g., GetCampaignByIdQuery)
class ViewCampaignDetailsHandler
{
    /**
     * Fetches a single campaign by its ID.
     * Includes relations like user and donations.
     *
     * @param int $campaignId
     * @return Campaign
     * @throws ModelNotFoundException
     */
    public function handle(int $campaignId): Campaign
    {
        // Eager load relations for efficiency
        return Campaign::with(['user', 'donations' => function ($query) {
            $query->where('payment_status', Donation::PAYMENT_STATUS_COMPLETED) // Only show completed donations
                  ->orderBy('created_at', 'desc');
        }])->findOrFail($campaignId);
    }
}