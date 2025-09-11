<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

#[Group('auth')]
class MeControllerTest extends TestCase
{
    use RefreshDatabase;

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
    public function me_route_exists(): void
    {
        $response = $this->getJson(route('auth.me'));
        $this->assertTrue(
            in_array($response->status(), [200, 401]),
            'Me route does not exist or returns unexpected status.'
        );
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
        $token = $this->createAndLoginUser();

        $response = $this->getJson(route('auth.me'), [
            'Authorization' => "Bearer {$token}"
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email'
            ])
            ->assertJsonMissing([
                'password',
                'remember_token'
            ]);
    }

    // -------------------------------
    // Sensitive data check
    // -------------------------------
    #[Test]
    public function me_does_not_return_sensitive_fields(): void
    {
        $token = $this->createAndLoginUser();

        $response = $this->getJson(route('auth.me'), [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertJsonMissing([
            'password',
            'remember_token'
        ]);
    }
}
