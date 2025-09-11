<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

#[Group('auth')]
class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------
    // Helpers
    // -------------------------------
    private function createUser(string $email = 'test@example.com', string $password = 'secret123'): User
    {
        return User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }

    // -------------------------------
    // Endpoint existence
    // -------------------------------
    #[Test]
    public function login_route_exists(): void
    {
        $response = $this->postJson(route('auth.login'), []);
        $this->assertTrue(in_array($response->status(), [200, 401, 422]), 'Login route does not exist or returns unexpected status.');
    }

    // -------------------------------
    // Input validation
    // -------------------------------
    #[Test]
    public function login_requires_email_and_password(): void
    {
        $response = $this->postJson(route('auth.login'), []);
        $response->assertStatus(422)->assertJsonValidationErrors(['email', 'password']);
    }

    #[Test]
    public function login_requires_valid_email_format(): void
    {
        $response = $this->postJson(route('auth.login'), [
            'email' => 'invalid-email',
            'password' => 'secret',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    // -------------------------------
    // Invalid credentials
    // -------------------------------
    #[Test]
    public function login_returns_unauthorized_for_wrong_password(): void
    {
        $this->createUser();

        $response = $this->postJson(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)->assertJson([
            'success' => false,
            'message' => 'Unauthorized',
        ]);
    }

    #[Test]
    public function login_returns_unauthorized_for_non_existent_email(): void
    {
        $response = $this->postJson(route('auth.login'), [
            'email' => 'noone@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(401)->assertJson([
            'success' => false,
            'message' => 'Unauthorized',
        ]);
    }

    // -------------------------------
    // Successful login
    // -------------------------------
    #[Test]
    public function login_returns_token_for_valid_credentials(): void
    {
        $this->createUser();

        $response = $this->postJson(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'secret123',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'access_token', 'token_type', 'expires_in'])
            ->assertJson(['success' => true]);
    }

    // -------------------------------
    // Security: no sensitive info
    // -------------------------------
    #[Test]
    public function login_does_not_return_password_or_sensitive_info(): void
    {
        $this->createUser();

        $response = $this->postJson(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'secret123',
        ]);

        $response->assertJsonMissing(['password', 'remember_token']);
    }

    // -------------------------------
    // Rate limiting / brute-force protection
    // -------------------------------
    #[Test]
    public function login_is_rate_limited(): void
    {
        $user = $this->createUser();

        // Simulate allowed failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('auth.login'), [
                'email' => $user->email,
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should be blocked
        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429)->assertJson(['message' => 'Too Many Attempts.']);
    }

    #[Test]
    public function login_rate_limit_is_per_email(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        // 5 failed attempts for user1
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('auth.login'), [
                'email' => $user1->email,
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt for user1 blocked
        $response1 = $this->postJson(route('auth.login'), [
            'email' => $user1->email,
            'password' => 'wrongpassword',
        ]);
        $response1->assertStatus(429)->assertJson(['message' => 'Too Many Attempts.']);

        // Attempt for user2 should not be blocked
        $response2 = $this->postJson(route('auth.login'), [
            'email' => $user2->email,
            'password' => 'wrongpassword',
        ]);
        $response2->assertStatus(401); // only invalid credentials, not blocked
    }
}
