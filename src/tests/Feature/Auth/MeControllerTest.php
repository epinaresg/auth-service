<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Adapters\Contracts\AuthAdapterInterface;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('auth')]
class MeControllerTest extends TestCase
{
    // -------------------------------
    // Helpers
    // -------------------------------
    private function issueAuthToken(User $user, string $password = 'password'): string
    {
        /** @var AuthAdapterInterface $auth */
        $auth = $this->app->make(AuthAdapterInterface::class);

        return $auth->authenticate($user->email, $password);
    }

    // -------------------------------
    // Endpoint existence
    // -------------------------------
    #[Test]
    public function me_route_exists(): void
    {
        $response = $this->getJson(route('auth.me'));
        $this->assertTrue(in_array($response->status(), [200, 401]), 'Me route does not exist or returns unexpected status.');
    }

    // -------------------------------
    // Authentication required
    // -------------------------------
    #[Test]
    public function me_requires_authentication(): void
    {
        $response = $this->getJson(route('auth.me'));
        $response->assertStatus(401);
    }

    // -------------------------------
    // Valid response
    // -------------------------------
    #[Test]
    public function me_returns_user_info_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $token = $this->issueAuthToken($user);

        $response = $this->getJson(route('auth.me'), [
            'Authorization' => "Bearer {$token}",
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email'],
            ])
            ->assertJsonMissing(['password', 'remember_token']);
    }

    // -------------------------------
    // Sensitive data check
    // -------------------------------
    #[Test]
    public function me_does_not_return_sensitive_fields(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $token = $this->issueAuthToken($user);

        $response = $this->getJson(route('auth.me'), [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertJsonMissing(['password', 'remember_token']);
    }
}
