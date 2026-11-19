<?php

namespace App\DTOs;

readonly class DashboardStatsData
{
    public function __construct(
        public int $total,
        public int $willApply,
        public int $applied,
        public int $interview,
        public int $noResponse,
        public int $offer,
        public int $rejected,
        public int $pendingReminders,
    ) {}

    /**
     * Convert to array for Inertia page prop serialization.
     *
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'willApply' => $this->willApply,
            'applied' => $this->applied,
            'interview' => $this->interview,
            'interviews' => $this->interview,
            'noResponse' => $this->noResponse,
            'offer' => $this->offer,
            'offers' => $this->offer,
            'rejected' => $this->rejected,
            'pendingReminders' => $this->pendingReminders,
        ];
    }
}
