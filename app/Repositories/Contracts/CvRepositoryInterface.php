<?php

namespace App\Repositories\Contracts;

use App\Models\Cv;
use Illuminate\Database\Eloquent\Collection;

interface CvRepositoryInterface
{
    /**
     * Create a CV for a user.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createForUser(int $userId, array $attributes): Cv;

    /**
     * Get all CVs belonging to a user.
     *
     * @return Collection<int, Cv>
     */
    public function getForUser(int $userId): Collection;

    /**
     * Find a CV by ID and user ID.
     */
    public function findByIdAndUser(int $id, int $userId): ?Cv;

    /**
     * Delete a CV.
     */
    public function delete(Cv $cv): bool;
}
