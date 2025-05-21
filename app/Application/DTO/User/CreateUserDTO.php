<?php

namespace App\Application\DTO\User;

class CreateUserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password, // Expects already hashed password
        public readonly string $role,
        public readonly bool $isActive
    ) {}
}
