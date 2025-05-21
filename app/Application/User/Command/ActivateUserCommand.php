<?php

namespace App\Application\User\Command;

class ActivateUserCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $adminId
    ) {}
}
