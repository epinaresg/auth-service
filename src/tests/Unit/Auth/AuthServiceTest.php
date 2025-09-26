<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Adapters\Contracts\AuthAdapterInterface;
use App\Services\AuthService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private $authService;
    private $authAdapter;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var MockInterface&AuthAdapterInterface */
        $this->authAdapter = Mockery::mock(AuthAdapterInterface::class);
        $this->authService = new AuthService($this->authAdapter);
    }

    #[Test]
    public function authenticate_returns_token_for_valid_credentials(): void
    {
        $this->authAdapter->shouldReceive('authenticate')->once()->with('test@example.com', 'secret123')->andReturn('faketoken');

        $token = $this->authService->authenticate('test@example.com', 'secret123');

        $this->assertEquals('faketoken', $token);
    }

    #[Test]
    public function authenticate_returns_null_for_invalid_credentials(): void
    {
        $this->authAdapter->shouldReceive('authenticate')->once()->with('test@example.com', 'wrongpassword')->andReturn(null);

        $token = $this->authService->authenticate('test@example.com', 'wrongpassword');

        $this->assertNull($token);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
