<?php

namespace App\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Infrastructure\Persistence\Models\Campaign; // Make sure this is your Eloquent Campaign model

/**
 * @mixin Campaign
 */
class CampaignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Campaign $campaign The campaign model instance */
        $campaign = $this->resource;

        return [
            'id' => $campaign->id,
            'title' => $campaign->title,
            'description' => $campaign->description,
            'goal_amount' => (float) $campaign->goal_amount,
            'current_amount' => (float) $campaign->current_amount,

            'start_date' => $campaign->start_date ? $campaign->start_date->toIso8601String() : null,
            'end_date' => $campaign->end_date ? $campaign->end_date->toIso8601String() : null,
            'status' => $campaign->status,
            'is_active' => $campaign->status === Campaign::STATUS_APPROVED
                && $campaign->start_date && $campaign->start_date <= now()
                && $campaign->end_date && $campaign->end_date >= now(),

            'is_funded' => (float)$campaign->current_amount >= (float)$campaign->goal_amount,

            'creator' => new UserResource($this->whenLoaded('user')),
            'donations' => DonationResource::collection($this->whenLoaded('donations')),

            'donations_count' => $this->whenLoaded('donations', function() use ($campaign) {
                return $campaign->donations->count();
            }, $this->donations_count ?? ($campaign->donations_count ?? 0)), // Fallback to model's donations_count if available

            'created_at' => $campaign->created_at ? $campaign->created_at->toIso8601String() : null,
            'updated_at' => $campaign->updated_at ? $campaign->updated_at->toIso8601String() : null,
        ];
    }
}
