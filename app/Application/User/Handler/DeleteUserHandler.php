<?php

namespace App\Application\User\Handler;

use App\Application\User\Command\DeleteUserCommand;
use App\Exceptions\UserNotFoundException;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteUserHandler
{
    public function handle(DeleteUserCommand $command): void
    {
        $user = User::find($command->userId);

        if (!$user) {
            throw new UserNotFoundException("User with ID {$command->userId} not found.");
        }

        if ($user->id === $command->adminId) {
            throw new \Exception("Admin cannot delete their own account.");
        }

        DB::transaction(function () use ($user, $command) {
            $userEmail = $user->email;
            $user->delete();
            Log::info("User {$command->userId} ({$userEmail}) deleted by admin {$command->adminId}.");
        });
    }
}
