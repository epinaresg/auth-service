<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Adapters\Contracts\AuthAdapterInterface;
use App\Exceptions\Auth\TokenRefreshException;
use App\Exceptions\Auth\TokenRefreshExpiredException;
use App\Exceptions\Auth\TokenRefreshInvalidException;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('auth')]
class RefreshTokenControllerTest extends TestCase
{
    private string $guardType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guardType = config('auth.defaults.guard'); // 'jwt' o 'passport'
    }

    // -------------------------------
    // Helpers
    // -------------------------------
    private function mockAuthToken(?callable $refreshReturn = null): string
    {
        $token = bin2hex(random_bytes(32));
        $refreshed = bin2hex(random_bytes(32));

        $mock = $this->createMock(AuthAdapterInterface::class);

        $mock->method('authenticate')->willReturn($token);

        if ($refreshReturn !== null) {
            $mock->method('refresh')->willReturnCallback($refreshReturn);
        } else {
            $mock->method('refresh')->willReturn($refreshed);
        }

        $this->app->instance(AuthAdapterInterface::class, $mock);

        return $token;
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
        $response->assertStatus(401)->assertJson(['success' => false, 'message' => 'The token could not be refreshed.']);
    }

    // -------------------------------
    // Successful token refresh
    // -------------------------------
    #[Test]
    public function refresh_returns_new_token_for_valid_authenticated_user(): void
    {
        $token = $this->mockAuthToken();

        $response = $this->postJson(route('auth.refresh'), [], ['Authorization' => "Bearer {$token}"]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'access_token', 'token_type', 'expires_in'])
            ->assertJson(['success' => true]);

        $this->assertNotEquals($token, $response->json('access_token'));
    }

    // -------------------------------
    // Expired token handling
    // -------------------------------
    #[Test]
    public function refresh_returns_unauthorized_for_expired_token(): void
    {
        $token = $this->mockAuthToken(function () {
            throw new TokenRefreshExpiredException();
        });

        $response = $this->postJson(route('auth.refresh'), [], ['Authorization' => "Bearer {$token}"]);

        $response->assertStatus(401)->assertJson(['success' => false, 'message' => 'The token has expired. You must log in again.']);
    }

    // -------------------------------
    // Invalid token handling
    // -------------------------------
    #[Test]
    public function refresh_returns_unauthorized_for_invalid_token(): void
    {
        if ($this->guardType === 'passport') {
            $this->markTestSkipped('Con Passport se omite este test.');
        }

        $token = $this->mockAuthToken(function () {
            throw new TokenRefreshInvalidException();
        });

        $response = $this->postJson(route('auth.refresh'), [], ['Authorization' => "Bearer {$token}"]);

        $response->assertStatus(401)->assertJson(['success' => false, 'message' => 'The token is invalid.']);
    }

    // -------------------------------
    // JWTException / Unexpected error handling
    // -------------------------------
    #[Test]
    public function refresh_returns_unauthorized_when_token_refresh_fails(): void
    {
        if ($this->guardType === 'passport') {
            $this->markTestSkipped('Con Passport se omite este test.');
        }

        $token = $this->mockAuthToken(function () {
            throw new TokenRefreshException();
        });

        $response = $this->postJson(route('auth.refresh'), [], ['Authorization' => "Bearer {$token}"]);

        $response->assertStatus(401)->assertJson(['success' => false, 'message' => 'The token could not be refreshed.']);
    }

    #[Test]
    public function refresh_returns_500_on_unexpected_error(): void
    {
        $token = $this->mockAuthToken(function () {
            throw new Exception('Unexpected error.');
        });

        $response = $this->postJson(route('auth.refresh'), [], ['Authorization' => "Bearer {$token}"]);

        $response->assertStatus(500)->assertJson([
            'success' => false,
            'message' => 'Unexpected error.',
        ]);
    }
}
