<?php

namespace App\Application\User\Handler;

use App\Application\User\Command\ActivateUserCommand;
use App\Exceptions\UserNotFoundException;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Log;

class ActivateUserHandler
{
    public function handle(ActivateUserCommand $command): User
    {
        $user = User::find($command->userId);

        if (!$user) {
            throw new UserNotFoundException("User with ID {$command->userId} not found.");
        }

        $user->is_active = true;
        $user->banned_at = null;
        $user->ban_reason = null;
        $user->save();

        Log::info("User {$user->id} ({$user->email}) activated by admin {$command->adminId}.");

        // Dispatch an event if needed: event(new UserActivated($user, $command->adminId));

        return $user->refresh();
    }
}
