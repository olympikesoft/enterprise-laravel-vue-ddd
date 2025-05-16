<?php

namespace App\Application\Donation\Command;

use App\Application\DTO\Donation\MakeDonationDTO;

class CreateDonationCommand
{
    public function __construct(public readonly MakeDonationDTO $dto)
    {
    }
}