<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/settings/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_profile_information_can_be_updated_with_username()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'name' => 'Test User',
                'username' => 'testuser123',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('testuser123', $user->username);
        $this->assertSame('test@example.com', $user->email);
    }

    public function test_username_must_be_unique()
    {
        $existingUser = User::factory()->create(['username' => 'existinguser']);
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'name' => 'Test User',
                'username' => 'existinguser',
                'email' => 'test@example.com',
            ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_username_can_be_null()
    {
        $user = User::factory()->create(['username' => 'oldusername']);

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'name' => 'Test User',
                'username' => '',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/profile');

        $user->refresh();

        $this->assertNull($user->username);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/settings/profile', [
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_email_must_be_provided_to_delete_account()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/settings/profile')
            ->delete('/settings/profile', [
                'email' => 'wrong@email.com',
            ]);

        $response
            ->assertSessionHasErrors('email')
            ->assertRedirect('/settings/profile');

        $this->assertNotNull($user->fresh());
    }
}
