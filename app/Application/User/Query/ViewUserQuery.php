<?php

namespace App\Application\User\Query;

class ViewUserQuery
{
    public function __construct(
        public readonly int $userId
    ) {}
}
