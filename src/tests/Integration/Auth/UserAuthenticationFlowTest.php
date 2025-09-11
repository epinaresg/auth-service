<?php

declare(strict_types=1);

namespace Tests\Integration\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserAuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function full_login_flow(): void
    {
        // Create user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('secret123'),
        ]);

        // Login
        $loginResponse = $this->postJson(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'secret123',
        ]);
        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('access_token');

        // Access "me" endpoint with token
        $meResponse = $this->getJson(route('auth.me'), [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $meResponse->assertStatus(200)->assertJson(['id' => $user->id, 'email' => $user->email]);

        // Refresh token
        $refreshResponse = $this->postJson(
            route('auth.refresh'),
            [],
            [
                'Authorization' => 'Bearer ' . $token,
            ],
        );
        $refreshResponse->assertStatus(200);

        // Logout
        $logoutResponse = $this->postJson(
            route('auth.logout'),
            [],
            [
                'Authorization' => 'Bearer ' . $token,
            ],
        );
        $logoutResponse->assertStatus(204);
    }
}
