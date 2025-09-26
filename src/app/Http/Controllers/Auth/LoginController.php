<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService)
    {
    }

    public function __invoke(LoginRequest $request): JsonResponse
    {
        ['email' => $email, 'password' => $password] = $request->validated();
        $token = $this->authService->authenticate($email, $password);
        if (!$token) {
            throw new UnauthorizedException();
        }

        return $this->respondWithToken($token, $this->authService->getTTLInSeconds());
    }
}
