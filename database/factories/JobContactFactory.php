<?php

namespace Database\Factories;

use App\Enums\JobContactStatus;
use App\Models\JobContact;
use App\Models\TrackedJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobContact>
 */
class JobContactFactory extends Factory
{
    protected $model = JobContact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_id' => TrackedJob::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'position' => fake()->jobTitle(),
            'status' => fake()->randomElement(JobContactStatus::cases()),
            'notes' => fake()->sentence(),
            'linkedin_url' => 'https://linkedin.com/in/'.fake()->userName(),
        ];
    }
}
