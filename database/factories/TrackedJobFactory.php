<?php

namespace Database\Factories;

use App\Enums\TrackedJobStatus;
use App\Models\Cv;
use App\Models\TrackedJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrackedJob>
 */
class TrackedJobFactory extends Factory
{
    protected $model = TrackedJob::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'jobTitle' => fake()->jobTitle(),
            'companyName' => fake()->company(),
            'jobURL' => fake()->url(),
            'status' => fake()->randomElement(TrackedJobStatus::cases()),
            'cv_id' => fn (array $attributes) => Cv::factory()->create(['user_id' => $attributes['user_id']])->id,
            'notes' => fake()->paragraph(),
            'appliedAt' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
