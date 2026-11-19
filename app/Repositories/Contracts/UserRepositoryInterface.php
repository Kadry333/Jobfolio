<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Set or unset the user's default CV.
     */
    public function setDefaultCv(User $user, ?int $cvId): bool;

    /**
     * Find a user by ID.
     */
    public function findById(int $id): ?User;
}
