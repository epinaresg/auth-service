<?php

declare(strict_types=1);

namespace App\Adapters;

use App\Adapters\Contracts\AuthAdapterInterface;
use App\Exceptions\Auth\TokenRefreshException;
use App\Exceptions\Auth\TokenRefreshExpiredException;
use App\Exceptions\Auth\TokenRefreshInvalidException;
use App\Exceptions\UnexpectedException;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\Facades\Log;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTGuard;

class JWTAuthAdapter implements AuthAdapterInterface
{
    public function __construct(private readonly AuthFactory $auth)
    {
    }

    private function guard(): JWTGuard
    {
        return $this->auth->guard('api-jwt');
    }

    public function authenticate(string $username, string $password): ?string
    {
        $token = $this->guard()->attempt(['email' => $username, 'password' => $password]);
        if ($token) {
            Log::info('Usuario autenticado correctamente (JWT).', [
                'email' => $username,
            ]);
        } else {
            Log::warning('Intento de autenticación fallido (JWT).', [
                'email' => $username,
            ]);
        }

        return $token ?: null;
    }

    public function logout(): void
    {
        try {
            $this->guard()->logout();
            Log::info('Sesión cerrada correctamente (JWT).', [
                'user_id' => $this->user()?->id,
            ]);
        } catch (JWTException $e) {
            Log::error('Error durante el logout (JWT).', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function refresh(): string
    {
        $exceptionErrorMessage = '';
        try {
            $newToken = $this->guard()->refresh();

            Log::info('Token refrescado correctamente (JWT).', [
                'user_id' => $this->user()?->id,
            ]);

            return $newToken;
        } catch (TokenExpiredException $e) {
            $exceptionErrorMessage = $e->getMessage();
            throw new TokenRefreshExpiredException($e);
        } catch (TokenInvalidException $e) {
            $exceptionErrorMessage = $e->getMessage();
            throw new TokenRefreshInvalidException($e);
        } catch (JWTException $e) {
            $exceptionErrorMessage = $e->getMessage();
            throw new TokenRefreshException($e);
        } catch (Throwable $e) {
            $exceptionErrorMessage = $e->getMessage();
            throw new UnexpectedException($e);
        } finally {
            Log::error('Error durante el refresh de token (JWT).', [
                'error' => $exceptionErrorMessage,
            ]);
        }
    }

    public function getTTL(): int
    {
        return $this->guard()->factory()->getTTL();
    }

    public function user(): ?User
    {
        return $this->guard()->user();
    }
}
