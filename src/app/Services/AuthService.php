<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class AuthService
{
    public function __construct(private UserRepositoryInterface $userRepository, private AuthFactory $auth) {}

    public function authenticate(array $credentials): ?string
    {
        $token = $this->auth->guard('api')->attempt($credentials);
        return $token !== false ? $token : null;
    }
}
