<?php

namespace App\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Infrastructure\Persistence\Models\Donation;

/**
 * @mixin Donation
 */
class DonationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'campaign_id' => $this->resource->campaign_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'amount' => (float) $this->resource->amount,
            'message' => $this->resource->notes,
            'currency' => $this->resource->currency,
            'payment_status' => $this->resource->payment_status,
            'donated_at' => $this->resource->created_at ? $this->resource->created_at->toIso8601String() : null,
        ];
    }
}
