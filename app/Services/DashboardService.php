<?php

namespace App\Services;

use App\DTOs\DashboardStatsData;
use App\Enums\TrackedJobStatus;
use App\Models\Cv;
use App\Models\User;
use App\Repositories\Contracts\CvRepositoryInterface;
use App\Repositories\Contracts\JobReminderRepositoryInterface;
use App\Repositories\Contracts\TrackedJobRepositoryInterface;

class DashboardService
{
    public function __construct(
        protected TrackedJobRepositoryInterface $trackedJobRepository,
        protected JobReminderRepositoryInterface $jobReminderRepository,
        protected CvRepositoryInterface $cvRepository,
    ) {}

    /**
     * Gather all aggregated data and entities needed for the dashboard.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(User $user): array
    {
        $countsByStatus = $this->trackedJobRepository->getStatusCountsForUser($user->id);
        $totalJobs = $this->trackedJobRepository->countForUser($user->id);
        $pendingReminders = $this->jobReminderRepository->getPendingCountForUser($user->id);

        $stats = new DashboardStatsData(
            total: $totalJobs,
            willApply: $countsByStatus[TrackedJobStatus::WillApply->value] ?? 0,
            applied: $countsByStatus[TrackedJobStatus::Applied->value] ?? 0,
            interview: $countsByStatus[TrackedJobStatus::Interview->value] ?? 0,
            noResponse: $countsByStatus[TrackedJobStatus::NoResponse->value] ?? 0,
            offer: $countsByStatus[TrackedJobStatus::Offer->value] ?? 0,
            rejected: $countsByStatus[TrackedJobStatus::Rejected->value] ?? 0,
            pendingReminders: $pendingReminders,
        );

        $recentJobs = $this->trackedJobRepository->getRecentForUser($user->id, 15);
        $cvs = $this->cvRepository->getForUser($user->id)->map(fn (Cv $cv) => [
            'id' => $cv->id,
            'name' => $cv->name,
            'file_path' => $cv->file_path,
        ]);
        $upcomingReminders = $this->jobReminderRepository->getUpcomingForUser($user->id, 5);

        return [
            'stats' => $stats->toArray(),
            'recentJobs' => $recentJobs,
            'cvs' => $cvs,
            'upcomingReminders' => $upcomingReminders,
            'statuses' => TrackedJobStatus::values(),
        ];
    }
}
