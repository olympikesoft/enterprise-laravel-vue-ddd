<?php

namespace App\Infrastructure\Event;

use App\Infrastructure\Persistence\Models\Donation;

class DonationMadeEvent
{
    public function __construct(
        public Donation $donation,
    ) {
    }
}
