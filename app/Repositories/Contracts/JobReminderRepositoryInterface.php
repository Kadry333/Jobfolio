<?php

namespace App\Repositories\Contracts;

use App\Models\JobReminder;
use Illuminate\Database\Eloquent\Collection;

interface JobReminderRepositoryInterface
{
    /**
     * Get upcoming reminders for a user.
     *
     * @return Collection<int, JobReminder>
     */
    public function getUpcomingForUser(int $userId, int $limit = 5): Collection;

    /**
     * Get count of pending reminders for a user.
     */
    public function getPendingCountForUser(int $userId): int;
}
