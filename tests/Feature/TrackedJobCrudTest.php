<?php

namespace Tests\Feature;

use App\Enums\TrackedJobStatus;
use App\Models\TrackedJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackedJobCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_jobs_index_page(): void
    {
        $user = User::factory()->create();
        TrackedJob::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('jobs.index'));

        $response->assertOk();
    }

    public function test_jobs_index_is_paginated_with_10_per_page(): void
    {
        $user = User::factory()->create();
        TrackedJob::factory()->count(15)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('jobs/Index')
            ->has('jobs.data', 10)
            ->where('jobs.total', 15)
            ->where('jobs.per_page', 10)
        );
    }

    public function test_user_can_create_tracked_job_and_is_redirected_to_jobs_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'jobTitle' => 'Backend Engineer',
            'companyName' => 'Stripe',
            'status' => 'will apply',
            'cv_selection' => 'none',
        ]);

        $response->assertRedirect(route('jobs.index'));
        $response->assertSessionHas('success', 'Job application added successfully.');

        $this->assertDatabaseHas('tracked_jobs', [
            'user_id' => $user->id,
            'jobTitle' => 'Backend Engineer',
            'companyName' => 'Stripe',
        ]);
    }

    public function test_user_can_view_create_job_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('jobs.create'));

        $response->assertOk();
    }

    public function test_user_can_view_job_detail_page(): void
    {
        $user = User::factory()->create();
        $job = TrackedJob::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('jobs.show', $job));

        $response->assertOk();
    }

    public function test_user_cannot_view_other_users_job(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $job = TrackedJob::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->get(route('jobs.show', $job));

        $response->assertForbidden();
    }

    public function test_user_can_view_edit_job_page(): void
    {
        $user = User::factory()->create();
        $job = TrackedJob::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('jobs.edit', $job));

        $response->assertOk();
    }

    public function test_user_can_update_tracked_job(): void
    {
        $user = User::factory()->create();
        $job = TrackedJob::factory()->create([
            'user_id' => $user->id,
            'jobTitle' => 'Junior Dev',
            'status' => TrackedJobStatus::WillApply,
        ]);

        $response = $this->actingAs($user)->patch(route('jobs.update', $job), [
            'jobTitle' => 'Mid-level Dev',
            'companyName' => $job->companyName,
            'status' => TrackedJobStatus::Applied->value,
            'notes' => 'Updated notes',
        ]);

        $response->assertRedirect(route('jobs.index'));

        $this->assertDatabaseHas('tracked_jobs', [
            'id' => $job->id,
            'jobTitle' => 'Mid-level Dev',
            'status' => 'applied',
            'notes' => 'Updated notes',
        ]);
    }

    public function test_user_can_delete_tracked_job(): void
    {
        $user = User::factory()->create();
        $job = TrackedJob::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('jobs.destroy', $job));

        $response->assertRedirect(route('jobs.index'));

        $this->assertDatabaseMissing('tracked_jobs', [
            'id' => $job->id,
        ]);
    }

    public function test_user_can_create_job_with_whatsapp_number(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'jobTitle' => 'Flutter Developer',
            'companyName' => 'Cairo Tech',
            'status' => 'will apply',
            'jobURL' => '+201012345678',
            'cv_selection' => 'none',
        ]);

        $response->assertRedirect(route('jobs.index'));
        $this->assertDatabaseHas('tracked_jobs', [
            'user_id' => $user->id,
            'jobTitle' => 'Flutter Developer',
            'jobURL' => '+201012345678',
        ]);
    }

    public function test_user_can_create_job_with_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'jobTitle' => 'Frontend Engineer',
            'companyName' => 'Vercel',
            'status' => 'applied',
            'jobURL' => 'https://company.com/careers/frontend',
            'cv_selection' => 'none',
        ]);

        $response->assertRedirect(route('jobs.index'));
        $this->assertDatabaseHas('tracked_jobs', [
            'user_id' => $user->id,
            'jobURL' => 'https://company.com/careers/frontend',
        ]);
    }

    public function test_job_fails_validation_with_invalid_url_or_number(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'jobTitle' => 'Developer',
            'companyName' => 'Acme',
            'status' => 'applied',
            'jobURL' => 'not-a-valid-url-or-number',
            'cv_selection' => 'none',
        ]);

        $response->assertSessionHasErrors(['jobURL']);
    }
}
