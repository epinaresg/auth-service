<?php

declare(strict_types=1);

namespace App\Adapters\Contracts;

use App\Models\User;

interface AuthAdapterInterface
{
    public function authenticate(string $username, string $password): ?string;

    public function logout(): void;

    public function refresh(): string;

    public function getTTL(): int;

    public function user(): ?User;
}
