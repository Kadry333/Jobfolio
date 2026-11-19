<?php

namespace App\Repositories\Eloquent;

use App\Models\TrackedJob;
use App\Repositories\Contracts\TrackedJobRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentTrackedJobRepository implements TrackedJobRepositoryInterface
{
    public function createForUser(int $userId, array $attributes): TrackedJob
    {
        $attributes['user_id'] = $userId;

        return TrackedJob::create($attributes);
    }

    public function update(TrackedJob $job, array $attributes): bool
    {
        return $job->update($attributes);
    }

    public function delete(TrackedJob $job): bool
    {
        return (bool) $job->delete();
    }

    public function findById(int $id): ?TrackedJob
    {
        return TrackedJob::find($id);
    }

    public function getRecentForUser(int $userId, int $limit = 15): Collection
    {
        return TrackedJob::query()
            ->where('user_id', $userId)
            ->with(['cv', 'contacts', 'reminder'])
            ->latest('id')
            ->take($limit)
            ->get();
    }

    public function getAllForUser(int $userId, ?string $status = null, ?string $search = null): Collection
    {
        return TrackedJob::query()
            ->where('user_id', $userId)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('jobTitle', 'like', "%{$search}%")
                        ->orWhere('companyName', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->with(['cv', 'contacts', 'reminder'])
            ->latest('id')
            ->get();
    }

    public function getPaginatedForUser(int $userId, int $perPage = 10, ?string $status = null, ?string $search = null): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, TrackedJob> $paginator */
        $paginator = TrackedJob::query()
            ->where('user_id', $userId)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('jobTitle', 'like', "%{$search}%")
                        ->orWhere('companyName', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->with(['cv', 'contacts', 'reminder'])
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return $paginator;
    }

    public function getStatusCountsForUser(int $userId): array
    {
        /** @var array<string, int> $results */
        $results = TrackedJob::query()
            ->where('user_id', $userId)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($val) => (int) $val)
            ->all();

        return $results;
    }

    public function countForUser(int $userId): int
    {
        return TrackedJob::query()
            ->where('user_id', $userId)
            ->count();
    }
}
