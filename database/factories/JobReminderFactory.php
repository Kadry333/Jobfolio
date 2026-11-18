<?php

namespace Database\Factories;

use App\Models\JobReminder;
use App\Models\TrackedJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobReminder>
 */
class JobReminderFactory extends Factory
{
    protected $model = JobReminder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_id' => TrackedJob::factory(),
            'scheduled_at' => fake()->dateTimeBetween('now', '+2 weeks'),
            'sent_at' => null,
            'status' => 'pending',
        ];
    }
}
