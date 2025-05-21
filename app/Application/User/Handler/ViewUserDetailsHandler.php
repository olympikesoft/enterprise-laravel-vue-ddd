<?php

namespace App\Application\User\Handler;

use App\Application\User\Query\ViewUserQuery;
use App\Exceptions\UserNotFoundException;
use App\Infrastructure\Persistence\Models\User;

class ViewUserDetailsHandler
{
    public function handle(ViewUserQuery $query): User
    {
        $user = User::find($query->userId);

        if (!$user) {
            throw new UserNotFoundException("User with ID {$query->userId} not found.");
        }

        return $user;
    }
}
