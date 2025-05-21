<?php

namespace App\Domain\User\Repository;

interface UserRepositoryInterface
{
    /**
     * Find a user by their ID.
     *
     * @param int $id
     * @return mixed
     */
    public function findById(int $id);

    /**
     * Find a user by their email.
     *
     * @param string $email
     * @return mixed
     */
    public function findByEmail(string $email);

    /**
     * Create a new user.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update an existing user.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update(int $id, array $data);

    /**
     * Delete a user by their ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
