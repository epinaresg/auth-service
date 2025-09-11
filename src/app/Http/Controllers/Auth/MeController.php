<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class MeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(JWTAuth::user());
    }
}
