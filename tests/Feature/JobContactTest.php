<?php

namespace Tests\Feature;

use App\Enums\JobContactStatus;
use App\Enums\TrackedJobStatus;
use App\Models\JobContact;
use App\Models\TrackedJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_job_with_contacts(): void
    {
        $user = User::factory()->create();

        $payload = [
            'jobTitle' => 'Staff Backend Engineer',
            'companyName' => 'Acme Corp',
            'status' => TrackedJobStatus::Applied->value,
            'notes' => 'Applied through referral',
            'contacts' => [
                [
                    'name' => 'Alice Johnson',
                    'email' => 'alice@acme.com',
                    'position' => 'Senior Recruiter',
                    'status' => JobContactStatus::MessageSent->value,
                    'notes' => 'Reached out on LinkedIn first',
                    'linkedin_url' => 'https://linkedin.com/in/alice-recruiter',
                ],
                [
                    'name' => 'Bob Smith',
                    'email' => 'bob@acme.com',
                    'position' => 'Engineering Manager',
                    'status' => JobContactStatus::WillContact->value,
                    'notes' => 'Hiring manager for team',
                    'linkedin_url' => 'https://linkedin.com/in/bob-em',
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('jobs.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('tracked_jobs', [
            'user_id' => $user->id,
            'jobTitle' => 'Staff Backend Engineer',
            'companyName' => 'Acme Corp',
        ]);

        $job = TrackedJob::where('jobTitle', 'Staff Backend Engineer')->firstOrFail();

        $this->assertDatabaseHas('job_contacts', [
            'job_id' => $job->id,
            'name' => 'Alice Johnson',
            'email' => 'alice@acme.com',
            'position' => 'Senior Recruiter',
            'status' => 'message sent',
        ]);

        $this->assertDatabaseHas('job_contacts', [
            'job_id' => $job->id,
            'name' => 'Bob Smith',
            'position' => 'Engineering Manager',
            'status' => 'will contact',
        ]);
    }

    public function test_user_can_add_contact_to_existing_job(): void
    {
        $user = User::factory()->create();
        $job = TrackedJob::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('jobs.contacts.store', $job), [
            'name' => 'Sarah Connor',
            'email' => 'sarah@skynet.com',
            'position' => 'Talent Lead',
            'status' => JobContactStatus::WillContact->value,
            'notes' => 'Initial screening scheduler',
            'linkedin_url' => 'https://linkedin.com/in/sarah-connor',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_contacts', [
            'job_id' => $job->id,
            'name' => 'Sarah Connor',
            'email' => 'sarah@skynet.com',
            'status' => 'will contact',
        ]);
    }

    public function test_user_cannot_add_contact_to_another_users_job(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $job = TrackedJob::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->post(route('jobs.contacts.store', $job), [
            'name' => 'Malicious Contact',
            'status' => JobContactStatus::WillContact->value,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('job_contacts', [
            'name' => 'Malicious Contact',
        ]);
    }

    public function test_user_can_update_contact(): void
    {
        $user = User::factory()->create();
        $job = TrackedJob::factory()->create(['user_id' => $user->id]);
        $contact = JobContact::factory()->create([
            'job_id' => $job->id,
            'name' => 'Old Name',
            'status' => JobContactStatus::WillContact,
        ]);

        $response = $this->actingAs($user)->put(route('contacts.update', $contact), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'position' => 'Head of HR',
            'status' => JobContactStatus::Replied->value,
            'notes' => 'Interview confirmed for Tuesday',
            'linkedin_url' => 'https://linkedin.com/in/updated',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_contacts', [
            'id' => $contact->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'position' => 'Head of HR',
            'status' => 'replied',
            'notes' => 'Interview confirmed for Tuesday',
        ]);
    }

    public function test_user_cannot_update_another_users_contact(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $job = TrackedJob::factory()->create(['user_id' => $user1->id]);
        $contact = JobContact::factory()->create([
            'job_id' => $job->id,
            'name' => 'Original Contact',
        ]);

        $response = $this->actingAs($user2)->put(route('contacts.update', $contact), [
            'name' => 'Hacked Contact',
            'status' => JobContactStatus::Replied->value,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('job_contacts', [
            'name' => 'Hacked Contact',
        ]);
    }

    public function test_user_can_delete_contact(): void
    {
        $user = User::factory()->create();
        $job = TrackedJob::factory()->create(['user_id' => $user->id]);
        $contact = JobContact::factory()->create([
            'job_id' => $job->id,
        ]);

        $response = $this->actingAs($user)->delete(route('contacts.destroy', $contact));

        $response->assertRedirect();
        $this->assertDatabaseMissing('job_contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_contact(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $job = TrackedJob::factory()->create(['user_id' => $user1->id]);
        $contact = JobContact::factory()->create([
            'job_id' => $job->id,
        ]);

        $response = $this->actingAs($user2)->delete(route('contacts.destroy', $contact));

        $response->assertForbidden();
        $this->assertDatabaseHas('job_contacts', [
            'id' => $contact->id,
        ]);
    }
}
