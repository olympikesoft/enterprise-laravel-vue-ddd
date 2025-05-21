<?php

namespace App\Application\DTO\User;

class UpdateUserDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $role,
        public readonly ?bool $isActive
    ) {}
}
