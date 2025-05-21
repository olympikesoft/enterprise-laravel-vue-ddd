<?php

namespace App\Application\User\Command;

class DeleteUserCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $adminId // ID of the admin performing the action
    ) {}
}
