<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('profile.edit'));

        $response->assertOk();
    }

    public function test_profile_name_can_be_updated(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'firstName' => 'UpdatedFirst',
                'lastName' => 'UpdatedLast',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('UpdatedFirst UpdatedLast', $user->name);
        $this->assertSame('original@example.com', $user->email);
    }

    public function test_email_cannot_be_changed(): void
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'firstName' => 'NewName',
                'lastName' => 'User',
                'email' => 'hacked@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('original@example.com', $user->email);
    }

    public function test_account_deletion_is_disabled(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/settings/profile', [
                'password' => 'password',
            ]);

        $response->assertMethodNotAllowed();
        $this->assertNotNull($user->fresh());
    }
}
