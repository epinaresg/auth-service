<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

trait ApiResponse
{
    /**
     * Return a successful JSON response.
     */
    protected function success(mixed $data, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json(
            [
                'success' => true,
                'data' => $data,
            ],
            $status,
        );
    }

    /**
     * Return an error JSON response.
     */
    protected function error(string $message, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json(
            [
                'success' => false,
                'message' => $message,
            ],
            $status,
        );
    }

    /**
     * Return a no-content response (204).
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Return a JSON response with a JWT token.
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60, // seconds
        ]);
    }
}
