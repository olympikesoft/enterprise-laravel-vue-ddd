<?php

namespace App\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Infrastructure\Persistence\Models\User;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User|null $requestUser */
        $requestUser = $request->user();

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'is_admin' => $this->when(
                $requestUser && $requestUser->is_admin,
                $this->resource->is_admin
            ), // Show only to admins
            'created_at' => $this->resource->created_at->toIso8601String(),
            'updated_at' => $this->resource->updated_at->toIso8601String(),
        ];
    }
}
