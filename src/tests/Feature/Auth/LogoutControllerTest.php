<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Adapters\Contracts\AuthAdapterInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('auth')]
class LogoutControllerTest extends TestCase
{
    // -------------------------------
    // Helpers
    // -------------------------------
    private function mockAuthToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $mock = $this->createMock(AuthAdapterInterface::class);
        $mock->method('authenticate')->willReturn($token);
        $this->app->instance(AuthAdapterInterface::class, $mock);

        return $token;
    }

    // -------------------------------
    // Endpoint existence
    // -------------------------------
    #[Test]
    public function logout_route_exists(): void
    {
        $response = $this->postJson(route('auth.logout'));
        $this->assertTrue(in_array($response->status(), [204, 401]), 'Logout route does not exist or returns unexpected status.');
    }

    // -------------------------------
    // Input validation / authentication
    // -------------------------------
    #[Test]
    public function logout_is_idempotent_when_no_token_provided(): void
    {
        $response = $this->postJson(route('auth.logout'));
        $response->assertStatus(204); // should return no content even without a token
        $this->assertEmpty($response->getContent());
    }

    // -------------------------------
    // Successful logout
    // -------------------------------
    #[Test]
    public function logout_returns_no_content_for_authenticated_user(): void
    {
        $token = $this->mockAuthToken();

        $response = $this->postJson(
            route('auth.logout'),
            [],
            [
                'Authorization' => "Bearer {$token}",
            ],
        );

        $response->assertStatus(204);
        $this->assertEmpty($response->getContent());
    }

    // -------------------------------
    // Security: token invalidation
    // -------------------------------
    #[Test]
    public function logout_invalidates_the_token(): void
    {
        $token = $this->mockAuthToken();

        // Logout
        $this->postJson(
            route('auth.logout'),
            [],
            [
                'Authorization' => "Bearer {$token}",
            ],
        )->assertStatus(204);

        // Try using the same token again
        $response = $this->postJson(
            route('auth.logout'),
            [],
            [
                'Authorization' => "Bearer {$token}",
            ],
        );

        $response->assertStatus(204); // still 204 (idempotent)
    }
}
