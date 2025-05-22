<?php

namespace App\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DonationCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = DonationResource::class; // Make sure DonationResource exists

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
