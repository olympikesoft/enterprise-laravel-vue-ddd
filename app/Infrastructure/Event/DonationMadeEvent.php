<?php

namespace App\Infrastructure\Event;

use App\Infrastructure\Persistence\Models\Donation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DonationMadeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The donation that was made.
     *
     * @var Donation
     */
    public $donation;

    /**
     * Create a new event instance.
     *
     * @param  Donation  $donation
     * @return void
     */
    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }
}
