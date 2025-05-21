<?php

namespace App\Application\User\Handler;

use App\Application\User\Command\UpdateUserCommand;
use App\Exceptions\UserNotFoundException;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateUserHandler
{
    public function handle(UpdateUserCommand $command): User
    {
        $user = User::find($command->userIdToUpdate);

        if (!$user) {
            throw new UserNotFoundException("User with ID {$command->userIdToUpdate} not found.");
        }

        $dataToValidate = [];
        if ($command->dto->name !== null) $dataToValidate['name'] = $command->dto->name;
        if ($command->dto->email !== null) $dataToValidate['email'] = $command->dto->email;
        if ($command->dto->role !== null) $dataToValidate['role'] = $command->dto->role;
        // isActive is a boolean, doesn't need validation in this context beyond type hint

        $validator = Validator::make($dataToValidate, [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'role' => 'sometimes|required|string|in:user,admin', // Adjust roles as needed
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::transaction(function () use ($user, $command) {
            $dataToUpdate = [];
            $changedFields = [];

            if ($command->dto->name !== null && $user->name !== $command->dto->name) {
                $dataToUpdate['name'] = $command->dto->name;
                $changedFields['name'] = ['old' => $user->name, 'new' => $command->dto->name];
            }
            if ($command->dto->email !== null && $user->email !== $command->dto->email) {
                $dataToUpdate['email'] = $command->dto->email;
                $changedFields['email'] = ['old' => $user->email, 'new' => $command->dto->email];
            }
            if ($command->dto->role !== null && $user->role !== $command->dto->role) {
                if ($user->id === $command->adminId && $command->dto->role !== 'admin') {
                    throw new \Exception("Admin cannot change their own role from admin.");
                }
                $dataToUpdate['role'] = $command->dto->role;
                $changedFields['role'] = ['old' => $user->role, 'new' => $command->dto->role];
            }
            if ($command->dto->isActive !== null && $user->is_active !== $command->dto->isActive) {
                $dataToUpdate['is_active'] = $command->dto->isActive;
                $changedFields['is_active'] = ['old' => $user->is_active, 'new' => $command->dto->isActive];
                if (!$command->dto->isActive && $user->is_active) {
                    if ($user->banned_at) {
                        $dataToUpdate['banned_at'] = null;
                        $dataToUpdate['ban_reason'] = null;
                        $changedFields['banned_at'] = ['old' => $user->banned_at, 'new' => null];
                    }
                }
            }

            if (!empty($dataToUpdate)) {
                $user->update($dataToUpdate);
                Log::info("User {$user->id} ({$user->email}) updated by admin {$command->adminId}.", $changedFields);
            }
        });

        return $user->refresh();
    }
}
