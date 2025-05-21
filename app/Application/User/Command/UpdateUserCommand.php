<?php

namespace App\Application\User\Command;

use App\Application\DTO\User\UpdateUserDTO;

class UpdateUserCommand
{
    public function __construct(
        public readonly int $userIdToUpdate, // ID of the user to be updated
        public readonly int $adminId,        // ID of the admin performing the action
        public readonly UpdateUserDTO $dto
    ) {}
}
