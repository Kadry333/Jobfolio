<?php

namespace Tests\Feature;

use App\Enums\JobContactStatus;
use App\Enums\TrackedJobStatus;
use App\Models\Cv;
use App\Models\JobContact;
use App\Models\JobReminder;
use App\Models\TrackedJob;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TracklySchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_expected_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('cvs'));
        $this->assertTrue(Schema::hasTable('tracked_jobs'));
        $this->assertTrue(Schema::hasTable('job_contacts'));
        $this->assertTrue(Schema::hasTable('job_reminders'));
    }

    public function test_users_table_has_trackly_columns(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'name'));
        $this->assertTrue(Schema::hasColumns('users', [
            'id',
            'firstName',
            'lastName',
            'email',
            'password',
            'default_cv_id',
            'created_at',
        ]));
    }

    public function test_cvs_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('cvs', [
            'id',
            'user_id',
            'name',
            'file_path',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_tracked_jobs_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('tracked_jobs', [
            'id',
            'user_id',
            'jobTitle',
            'companyName',
            'jobURL',
            'status',
            'cv_id',
            'notes',
            'appliedAt',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_job_contacts_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('job_contacts', [
            'id',
            'job_id',
            'name',
            'email',
            'position',
            'status',
            'notes',
            'linkedin_url',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_job_reminders_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('job_reminders', [
            'id',
            'job_id',
            'scheduled_at',
            'sent_at',
            'status',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_user_relationships_with_cv_and_tracked_jobs(): void
    {
        $user = User::factory()->create([
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $cv1 = Cv::factory()->create(['user_id' => $user->id]);
        $cv2 = Cv::factory()->create(['user_id' => $user->id]);

        $user->update(['default_cv_id' => $cv1->id]);

        $job = TrackedJob::factory()->create([
            'user_id' => $user->id,
            'cv_id' => $cv1->id,
            'jobTitle' => 'Software Engineer',
            'companyName' => 'Acme Corp',
            'status' => 'applied',
        ]);

        $this->assertCount(2, $user->cvs);
        $this->assertEquals($cv1->id, $user->defaultCv->id);
        $this->assertCount(1, $user->trackedJobs);
        $this->assertEquals('Software Engineer', $user->trackedJobs->first()->jobTitle);
    }

    public function test_cv_relationships(): void
    {
        $user = User::factory()->create();
        $cv = Cv::factory()->create(['user_id' => $user->id]);

        $user->update(['default_cv_id' => $cv->id]);

        $job1 = TrackedJob::factory()->create(['user_id' => $user->id, 'cv_id' => $cv->id]);
        $job2 = TrackedJob::factory()->create(['user_id' => $user->id, 'cv_id' => $cv->id]);

        $this->assertEquals($user->id, $cv->user->id);
        $this->assertEquals($user->id, $cv->defaultForUser->id);
        $this->assertCount(2, $cv->trackedJobs);
    }

    public function test_tracked_job_relationships_with_contacts_and_reminders(): void
    {
        $job = TrackedJob::factory()->create([
            'jobTitle' => 'Lead Developer',
            'appliedAt' => '2026-03-01 10:00:00',
        ]);

        $contact = JobContact::factory()->create([
            'job_id' => $job->id,
            'name' => 'Alice Recruiter',
        ]);

        $reminder = JobReminder::factory()->create([
            'job_id' => $job->id,
            'scheduled_at' => '2026-03-08 09:00:00',
            'status' => 'pending',
        ]);

        $this->assertCount(1, $job->contacts);
        $this->assertEquals('Alice Recruiter', $job->contacts->first()->name);
        $this->assertEquals($job->id, $contact->trackedJob->id);

        $this->assertNotNull($job->reminder);
        $this->assertEquals($reminder->id, $job->reminder->id);
        $this->assertEquals($job->id, $reminder->trackedJob->id);

        $this->assertInstanceOf(CarbonInterface::class, $job->appliedAt);
        $this->assertInstanceOf(CarbonInterface::class, $reminder->scheduled_at);
    }

    public function test_tracked_job_and_contact_statuses_cast_to_enums(): void
    {
        $job = TrackedJob::factory()->create([
            'status' => 'will apply',
        ]);
        $this->assertInstanceOf(TrackedJobStatus::class, $job->status);
        $this->assertSame(TrackedJobStatus::WillApply, $job->status);

        $contact = JobContact::factory()->create([
            'job_id' => $job->id,
            'status' => 'message sent',
        ]);
        $this->assertInstanceOf(JobContactStatus::class, $contact->status);
        $this->assertSame(JobContactStatus::MessageSent, $contact->status);

        $this->assertEquals([
            'will apply',
            'applied',
            'interview',
            'no response',
            'offer',
            'rejected',
        ], TrackedJobStatus::values());

        $this->assertEquals([
            'will contact',
            'message sent',
            'replied',
            'no response',
        ], JobContactStatus::values());
    }

    public function test_user_deletion_cascades_to_cvs_and_tracked_jobs(): void
    {
        $user = User::factory()->create();
        $cv = Cv::factory()->create(['user_id' => $user->id]);
        $job = TrackedJob::factory()->create(['user_id' => $user->id, 'cv_id' => $cv->id]);
        $contact = JobContact::factory()->create(['job_id' => $job->id]);
        $reminder = JobReminder::factory()->create(['job_id' => $job->id]);

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('cvs', ['id' => $cv->id]);
        $this->assertDatabaseMissing('tracked_jobs', ['id' => $job->id]);
        $this->assertDatabaseMissing('job_contacts', ['id' => $contact->id]);
        $this->assertDatabaseMissing('job_reminders', ['id' => $reminder->id]);
    }

    public function test_cv_deletion_sets_null_on_user_default_cv_and_tracked_job(): void
    {
        $user = User::factory()->create();
        $cv = Cv::factory()->create(['user_id' => $user->id]);
        $user->update(['default_cv_id' => $cv->id]);
        $job = TrackedJob::factory()->create(['user_id' => $user->id, 'cv_id' => $cv->id]);

        $cv->delete();

        $this->assertDatabaseMissing('cvs', ['id' => $cv->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'default_cv_id' => null]);
        $this->assertDatabaseHas('tracked_jobs', ['id' => $job->id, 'cv_id' => null]);
    }
}
