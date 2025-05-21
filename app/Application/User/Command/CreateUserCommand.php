<?php

namespace App\Application\User\Command;

use App\Application\DTO\User\CreateUserDTO;

class CreateUserCommand
{
    public function __construct(
        public readonly CreateUserDTO $dto,
        public readonly int $adminId // ID of the admin performing the action
    ) {}
}
