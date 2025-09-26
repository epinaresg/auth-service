<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Adapters\Contracts\AuthAdapterInterface;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithAdapter
{
    use ApiResponse;

    public function handle(Request $request, Closure $next)
    {
        $user = app(AuthAdapterInterface::class)->user();

        if (!$user) {
            return $this->error('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
