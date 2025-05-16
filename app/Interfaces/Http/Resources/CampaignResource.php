<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'goal_amount' => (float) $this->goal_amount,
            'current_amount' => (float) $this->current_amount,
            'start_date' => $this->start_date->toIso8601String(),
            'end_date' => $this->end_date->toIso8601String(),
            'status' => $this->status,
            'days_remaining' => $this->end_date > now() ? $this->end_date->diffInDays(now()) : 0,
            'is_active' => $this->status === \App\Infrastructure\Persistence\Models\Campaign::STATUS_APPROVED && $this->start_date <= now() && $this->end_date >= now(),
            'is_funded' => (float)$this->current_amount >= (float)$this->goal_amount,
            'creator' => new UserResource($this->whenLoaded('user')),
            'donations' => DonationResource::collection($this->whenLoaded('donations')),
            'donations_count' => $this->whenLoaded('donations', function() {
                return $this->donations->count();
            }, $this->donations_count ?? 0),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}