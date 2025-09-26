<?php

declare(strict_types=1);

namespace App\Services;

use App\Adapters\Contracts\AuthAdapterInterface;

class AuthService
{
    public function __construct(private readonly AuthAdapterInterface $authAdapter)
    {
    }

    public function authenticate(string $username, string $password): ?string
    {
        return $this->authAdapter->authenticate($username, $password) ?: null;
    }

    public function logout(): void
    {
        $this->authAdapter->logout();
    }

    public function refresh(): string
    {
        return $this->authAdapter->refresh();
    }

    public function getTTLInMinutes(): int
    {
        return $this->authAdapter->getTTL();
    }

    public function getTTLInSeconds(): int
    {
        return $this->authAdapter->getTTL() * 60;
    }

    public function me(): mixed
    {
        return $this->authAdapter->user();
    }
}
