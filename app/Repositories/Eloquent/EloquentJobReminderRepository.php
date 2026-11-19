<?php

namespace App\Repositories\Eloquent;

use App\Models\JobReminder;
use App\Repositories\Contracts\JobReminderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentJobReminderRepository implements JobReminderRepositoryInterface
{
    public function getUpcomingForUser(int $userId, int $limit = 5): Collection
    {
        return JobReminder::query()
            ->whereHas('trackedJob', fn ($q) => $q->where('user_id', $userId))
            ->with('trackedJob:id,jobTitle,companyName')
            ->where('status', 'pending')
            ->orderBy('scheduled_at')
            ->take($limit)
            ->get();
    }

    public function getPendingCountForUser(int $userId): int
    {
        return JobReminder::query()
            ->whereHas('trackedJob', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 'pending')
            ->count();
    }
}
