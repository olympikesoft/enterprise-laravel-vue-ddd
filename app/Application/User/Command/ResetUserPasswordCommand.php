<?php

namespace App\Application\User\Command;

class ResetUserPasswordCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $adminId,
        public readonly string $newPassword
    ) {}
}
