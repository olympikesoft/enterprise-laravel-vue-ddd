use App\Domain\Repositories\UserRepositoryInterface;
use App\Models\User;

<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\Persistence\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * Find a user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
        }

        /**
         * Find a user by email.
         *
         * @param string $email
         * @return User|null
         */
        public function findByEmail(string $email): ?User
        {
            return User::where('email', $email)->first();
    }

    /**
     * Get all users.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAll()
    {
        return User::all();
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update a user by ID.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $user = $this->findById($id);

        if ($user) {
            return $user->update($data);
        }

        return false;
    }

    /**
     * Delete a user by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $user = $this->findById($id);

        if ($user) {
            return $user->delete();
        }

        return false;
    }
}
