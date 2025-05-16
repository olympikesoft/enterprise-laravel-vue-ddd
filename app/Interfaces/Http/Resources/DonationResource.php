<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'donor_name' => $this->donor_name, // Shows 'Anonymous Donor' if user_id was null and User model has withDefault
            'user' => new UserResource($this->whenLoaded('user')), // Only if user is loaded and not anonymous
            'amount' => (float) $this->amount,
            'message' => $this->message,
            'payment_status' => $this->payment_status,
            'donated_at' => $this->created_at->toIso8601String(),
            // Do not expose transaction_id or payment_gateway_response to general users
            // 'transaction_id' => $this->when($request->user() && ($request->user()->is_admin || $request->user()->id === $this->user_id), $this->transaction_id),
        ];
    }
}