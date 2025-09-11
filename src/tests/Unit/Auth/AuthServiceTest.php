<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private $authService;
    private $userRepositoryMock;
    private $authFactoryMock;
    private $guardMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->authFactoryMock = Mockery::mock(AuthFactory::class);
        $this->guardMock = Mockery::mock(Guard::class);

        $this->authFactoryMock->shouldReceive('guard')->andReturn($this->guardMock);

        $this->authService = new AuthService($this->userRepositoryMock, $this->authFactoryMock);
    }

    #[Test]
    public function authenticate_returns_token_for_valid_credentials(): void
    {
        $credentials = ['email' => 'test@example.com', 'password' => 'secret123'];

        $this->guardMock->shouldReceive('attempt')->once()->with($credentials)->andReturn('faketoken');

        $token = $this->authService->authenticate($credentials);

        $this->assertEquals('faketoken', $token);
    }

    #[Test]
    public function authenticate_returns_null_for_invalid_credentials(): void
    {
        $credentials = ['email' => 'test@example.com', 'password' => 'wrongpassword'];

        $this->guardMock->shouldReceive('attempt')->once()->with($credentials)->andReturn(false);

        $token = $this->authService->authenticate($credentials);

        $this->assertNull($token);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
