<?php

namespace App\Repositories\Contracts;

use App\Models\TrackedJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TrackedJobRepositoryInterface
{
    /**
     * Create a tracked job for a user.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createForUser(int $userId, array $attributes): TrackedJob;

    /**
     * Update an existing tracked job.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(TrackedJob $job, array $attributes): bool;

    /**
     * Delete a tracked job.
     */
    public function delete(TrackedJob $job): bool;

    /**
     * Find a tracked job by ID.
     */
    public function findById(int $id): ?TrackedJob;

    /**
     * Get recent tracked jobs for a user with related relations.
     *
     * @return Collection<int, TrackedJob>
     */
    public function getRecentForUser(int $userId, int $limit = 15): Collection;

    /**
     * Get all tracked jobs for a user with optional status and search filtering.
     *
     * @return Collection<int, TrackedJob>
     */
    public function getAllForUser(int $userId, ?string $status = null, ?string $search = null): Collection;

    /**
     * Get paginated tracked jobs for a user with optional status and search filtering.
     *
     * @return LengthAwarePaginator<int, TrackedJob>
     */
    public function getPaginatedForUser(int $userId, int $perPage = 10, ?string $status = null, ?string $search = null): LengthAwarePaginator;

    /**
     * Get job counts grouped by status.
     *
     * @return array<string, int>
     */
    public function getStatusCountsForUser(int $userId): array;

    /**
     * Get total job count for a user.
     */
    public function countForUser(int $userId): int;
}
