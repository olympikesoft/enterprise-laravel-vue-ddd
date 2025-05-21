<?php

namespace App\Application\User\Handler;

use App\Application\User\Command\CreateUserCommand;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateUserHandler
{
    public function handle(CreateUserCommand $command): User
    {
        $validator = Validator::make([
            'email' => $command->dto->email,
            'name' => $command->dto->name,
            'role' => $command->dto->role,
        ], [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'role' => 'required|string|in:user,admin', // Adjust roles as needed
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = DB::transaction(function () use ($command) {
            $newUser = User::create([
                'name' => $command->dto->name,
                'email' => $command->dto->email,
                'password' => $command->dto->password,
                'role' => $command->dto->role,
                'is_active' => $command->dto->isActive,
                'email_verified_at' => $command->dto->isActive ? now() : null, // Verify if active
            ]);

            Log::info("User {$newUser->id} ({$newUser->email}) created by admin {$command->adminId}.");
            return $newUser;
        });

        return $user;
    }
}
