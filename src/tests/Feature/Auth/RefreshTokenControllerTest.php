<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

#[Group('auth')]
class RefreshTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------
    // Helpers
    // -------------------------------
    private function createAndLoginUser(string $email = 'test@example.com', string $password = 'secret123'): string
    {
        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        return JWTAuth::fromUser($user);
    }

    // -------------------------------
    // Endpoint existence
    // -------------------------------
    #[Test]
    public function refresh_route_exists(): void
    {
        $response = $this->postJson(route('auth.refresh'));
        $this->assertTrue(in_array($response->status(), [200, 401]), 'Refresh route does not exist or returns unexpected status.');
    }

    // -------------------------------
    // Authentication
    // -------------------------------
    #[Test]
    public function refresh_requires_authentication(): void
    {
        $response = $this->postJson(route('auth.refresh'));
        $response->assertStatus(401)->assertJson(['success' => false, 'message' => 'No token provided.']);
    }

    // -------------------------------
    // Successful token refresh
    // -------------------------------
    #[Test]
    public function refresh_returns_new_token_for_valid_authenticated_user(): void
    {
        $token = $this->createAndLoginUser();

        $response = $this->postJson(
            route('auth.refresh'),
            [],
            [
                'Authorization' => "Bearer {$token}",
            ],
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'access_token', 'token_type', 'expires_in'])
            ->assertJson(['success' => true]);
    }

    // -------------------------------
    // Expired token handling
    // -------------------------------
    #[Test]
    public function refresh_returns_unauthorized_for_expired_token(): void
    {
        JWTAuth::shouldReceive('getToken')->once()->andReturn('faketoken');
        JWTAuth::shouldReceive('refresh')->once()->andThrow(new TokenExpiredException());

        $response = $this->postJson(
            route('auth.refresh'),
            [],
            [
                'Authorization' => 'Bearer faketoken',
            ],
        );

        $response->assertStatus(401)->assertJson(['success' => false, 'message' => 'The token has expired. You must log in again.']);
    }

    // -------------------------------
    // Invalid token handling
    // -------------------------------
    #[Test]
    public function refresh_returns_unauthorized_for_invalid_token(): void
    {
        JWTAuth::shouldReceive('getToken')->once()->andReturn('faketoken');
        JWTAuth::shouldReceive('refresh')->once()->andThrow(new TokenInvalidException());

        $response = $this->postJson(
            route('auth.refresh'),
            [],
            [
                'Authorization' => 'Bearer faketoken',
            ],
        );

        $response->assertStatus(401)->assertJson(['success' => false, 'message' => 'The token is invalid.']);
    }

    // -------------------------------
    // JWTException / Unexpected error handling
    // -------------------------------
    #[Test]
    public function refresh_returns_401_for_jwt_exception(): void
    {
        JWTAuth::shouldReceive('getToken')->once()->andReturn('faketoken');
        JWTAuth::shouldReceive('refresh')->once()->andThrow(new JWTException());

        $response = $this->postJson(
            route('auth.refresh'),
            [],
            [
                'Authorization' => 'Bearer faketoken',
            ],
        );

        $response->assertStatus(401)->assertJson([
            'success' => false,
            'message' => 'The token could not be refreshed.',
        ]);
    }

    #[Test]
    public function refresh_returns_500_on_unexpected_error(): void
    {
        JWTAuth::shouldReceive('getToken')->once()->andReturn('faketoken');
        JWTAuth::shouldReceive('refresh')->once()->andThrow(new \Exception('Unexpected error'));

        $response = $this->postJson(
            route('auth.refresh'),
            [],
            [
                'Authorization' => 'Bearer faketoken',
            ],
        );

        $response->assertStatus(500)->assertJson([
            'success' => false,
            'message' => 'Unexpected error.',
        ]);
    }
}
