<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService) {}

    public function __invoke(LoginRequest $request):JsonResponse
    {
        $token = $this->authService->authenticate($request->validated());
        if (!$token) {
            return $this->error('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }
        return $this->respondWithToken($token);
    }
}
