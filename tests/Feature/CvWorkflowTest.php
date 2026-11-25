<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CvWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_optional_onboarding_screen(): void
    {
        $user = User::factory()->create([
            'default_cv_id' => null,
        ]);

        $response = $this->actingAs($user)->get(route('onboarding.cv'));

        $response->assertOk();
    }

    public function test_user_with_default_cv_is_redirected_away_from_onboarding(): void
    {
        $user = User::factory()->create();
        $cv = Cv::factory()->create(['user_id' => $user->id]);
        $user->update(['default_cv_id' => $cv->id]);

        $response = $this->actingAs($user)->get(route('onboarding.cv'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_can_upload_cv_during_onboarding(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['default_cv_id' => null]);

        $file = UploadedFile::fake()->create('my_resume.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->post(route('onboarding.cv.store'), [
            'cv_file' => $file,
            'name' => 'Main Engineering Resume',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertNotNull($user->default_cv_id);

        $cv = Cv::where('id', $user->default_cv_id)->first();
        $this->assertNotNull($cv);
        $this->assertEquals('Main Engineering Resume', $cv->name);
        Storage::disk('public')->assertExists($cv->file_path);
    }

    public function test_applying_to_job_with_default_cv(): void
    {
        $user = User::factory()->create();
        $cv = Cv::factory()->create(['user_id' => $user->id]);
        $user->update(['default_cv_id' => $cv->id]);

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'jobTitle' => 'Backend Architect',
            'companyName' => 'Stripe',
            'status' => 'applied',
            'cv_selection' => 'default',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tracked_jobs', [
            'user_id' => $user->id,
            'jobTitle' => 'Backend Architect',
            'cv_id' => $cv->id,
        ]);
    }

    public function test_applying_to_job_with_new_uploaded_cv(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['default_cv_id' => null]);

        $file = UploadedFile::fake()->create('tailored_cv.pdf', 300, 'application/pdf');

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'jobTitle' => 'Staff Engineer',
            'companyName' => 'Google',
            'status' => 'will apply',
            'cv_selection' => 'new',
            'new_cv_file' => $file,
            'new_cv_name' => 'Tailored Staff Resume',
            'make_default' => 1,
        ]);

        $response->assertRedirect();

        $newCv = Cv::where('name', 'Tailored Staff Resume')->first();
        $this->assertNotNull($newCv);
        Storage::disk('public')->assertExists($newCv->file_path);

        $this->assertDatabaseHas('tracked_jobs', [
            'user_id' => $user->id,
            'jobTitle' => 'Staff Engineer',
            'cv_id' => $newCv->id,
        ]);

        $user->refresh();
        $this->assertEquals($newCv->id, $user->default_cv_id);
    }

    public function test_cv_management_routes(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $cv1 = Cv::factory()->create(['user_id' => $user->id]);
        $cv2 = Cv::factory()->create(['user_id' => $user->id]);

        // Set default
        $response = $this->actingAs($user)->patch(route('cvs.default', $cv2));
        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals($cv2->id, $user->default_cv_id);

        // Delete CV
        $response = $this->actingAs($user)->delete(route('cvs.destroy', $cv2));
        $response->assertRedirect();
        $user->refresh();
        $this->assertNull($user->default_cv_id);
        $this->assertDatabaseMissing('cvs', ['id' => $cv2->id]);
    }

    public function test_cv_upload_fails_if_exceeding_5mb(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // 6MB file
        $file = UploadedFile::fake()->create('large_resume.pdf', 6144, 'application/pdf');

        $response = $this->actingAs($user)->post(route('cvs.store'), [
            'cv_file' => $file,
            'name' => 'Too Large Resume',
        ]);

        $response->assertSessionHasErrors(['cv_file']);
    }

    public function test_user_cannot_upload_more_than_10_cvs(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // Create 10 existing CVs
        Cv::factory()->count(10)->create(['user_id' => $user->id]);

        $file = UploadedFile::fake()->create('eleventh_resume.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->post(route('cvs.store'), [
            'cv_file' => $file,
            'name' => '11th Resume',
        ]);

        $response->assertSessionHasErrors(['cv_file']);
        $this->assertDatabaseMissing('cvs', ['name' => '11th Resume']);
    }

    public function test_user_cannot_upload_more_than_10_cvs_via_job_create(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // Create 10 existing CVs
        Cv::factory()->count(10)->create(['user_id' => $user->id]);

        $file = UploadedFile::fake()->create('inline_eleventh_resume.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'jobTitle' => 'Lead Developer',
            'companyName' => 'Amazon',
            'status' => 'applied',
            'cv_selection' => 'new',
            'new_cv_file' => $file,
            'new_cv_name' => 'Inline 11th',
        ]);

        $response->assertSessionHasErrors(['new_cv_file']);
        $this->assertDatabaseMissing('cvs', ['name' => 'Inline 11th']);
    }
}
