<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class RefreshTokenController extends Controller
{
    use ApiResponse;

    public function __invoke(): JsonResponse
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return $this->error('No token provided.', Response::HTTP_UNAUTHORIZED);
            }
            return $this->respondWithToken(JWTAuth::refresh());
        } catch (\Throwable $e) {
            return match (true) {
                $e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException => $this->error('The token has expired. You must log in again.', Response::HTTP_UNAUTHORIZED),
                $e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException => $this->error('The token is invalid.', Response::HTTP_UNAUTHORIZED),
                $e instanceof \Tymon\JWTAuth\Exceptions\JWTException => $this->error('The token could not be refreshed.', Response::HTTP_UNAUTHORIZED),
                default => $this->error('Unexpected error.', Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
    }
}
