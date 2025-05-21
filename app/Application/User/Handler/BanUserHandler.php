<?php

namespace App\Application\User\Handler;

use App\Application\User\Command\BanUserCommand;
use App\Exceptions\UserNotFoundException;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Log;

class BanUserHandler
{
    public function handle(BanUserCommand $command): User
    {
        $user = User::find($command->userId);

        if (!$user) {
            throw new UserNotFoundException("User with ID {$command->userId} not found.");
        }

        if ($user->id === $command->adminId) {
            throw new \Exception("Admin cannot ban their own account.");
        }

        if ($user->banned_at) {
        }

        $user->banned_at = now();
        $user->ban_reason = $command->reason;
        $user->is_active = false;
        $user->save();

        Log::info("User {$user->id} ({$user->email}) banned by admin {$command->adminId}. Reason: {$command->reason}");


        return $user->refresh();
    }
}
