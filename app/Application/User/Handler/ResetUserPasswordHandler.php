<?php

namespace App\Application\User\Handler;

use App\Application\User\Command\ResetUserPasswordCommand;
use App\Exceptions\UserNotFoundException;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // For invalidating sessions

class ResetUserPasswordHandler
{
    public function handle(ResetUserPasswordCommand $command): User
    {
        $user = User::find($command->userId);

        if (!$user) {
            throw new UserNotFoundException("User with ID {$command->userId} not found.");
        }

        $user->password = $command->newPassword;
        $user->save();

        Log::info("Password for user {$user->id} ({$user->email}) reset by admin {$command->adminId}.");

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }
        // For Sanctum tokens, you might want to revoke them:
        // $user->tokens()->delete();

        // Dispatch an event if needed: event(new UserPasswordResetByAdmin($user, $command->adminId));

        return $user->refresh();
    }
}
