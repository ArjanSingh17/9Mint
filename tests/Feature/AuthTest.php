<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ── Register ─────────────────────────────────

    public function test_user_can_register()
    {
        $response = $this->post('/register', [
            'name'                  => 'testuser',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('homepage'));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_register_fails_with_duplicate_name()
    {
        // Register the user first
        User::factory()->create(['name' => 'testuser']);

        $response = $this->post('/register', [
            'name'                  => 'testuser',
            'email'                 => 'other@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // registerWeb uses 'register' error bag
        $response->assertSessionHasErrorsIn('register', 'name');
    }

    public function test_register_fails_with_duplicate_email()
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post('/register', [
            'name'                  => 'newuser',
            'email'                 => 'taken@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrorsIn('register', 'email');
    }

    public function test_reserved_name_cannot_be_registered_if_already_claimed()
    {
        // '9Mint' already exists with an email (already claimed)
        User::factory()->create(['name' => '9Mint', 'email' => 'claimed@example.com']);

        $response = $this->post('/register', [
            'name'                  => '9Mint',
            'email'                 => 'someone@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrorsIn('register', 'name');
    }

    // ── Login ─────────────────────────────────────

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'name'     => 'testuser',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'name'     => 'testuser',
            'password' => 'password123',
        ]);

        // loginWeb redirects to profile.settings
        $response->assertRedirect(route('profile.settings'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password()
    {
        User::factory()->create([
            'name'     => 'testuser',
            'password' => bcrypt('correctpassword'),
        ]);

        $response = $this->post('/login', [
            'name'     => 'testuser',
            'password' => 'wrongpassword',
        ]);

        // loginWeb uses 'login' error bag
        $response->assertSessionHasErrorsIn('login', 'name');
        $this->assertGuest();
    }

    public function test_login_fails_with_nonexistent_user()
    {
        $response = $this->post('/login', [
            'name'     => 'nobody',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrorsIn('login', 'name');
        $this->assertGuest();
    }

    // ── Logout ────────────────────────────────────

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}