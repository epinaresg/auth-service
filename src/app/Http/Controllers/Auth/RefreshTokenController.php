<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class RefreshTokenController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService)
    {
    }

    public function __invoke(): JsonResponse
    {
        $accessToken = $this->authService->refresh();
        return $this->respondWithToken($accessToken, $this->authService->getTTLInSeconds());
    }
}
