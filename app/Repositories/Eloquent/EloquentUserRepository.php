<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function setDefaultCv(User $user, ?int $cvId): bool
    {
        return $user->update(['default_cv_id' => $cvId]);
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }
}
