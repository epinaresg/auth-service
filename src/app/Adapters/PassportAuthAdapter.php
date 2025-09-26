<?php

declare(strict_types=1);

namespace App\Adapters;

use App\Adapters\Contracts\AuthAdapterInterface;
use App\Exceptions\Auth\TokenRefreshException;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Guards\TokenGuard;
use Throwable;

class PassportAuthAdapter implements AuthAdapterInterface
{
    public function __construct(private readonly AuthFactory $auth)
    {
    }

    private function guard(): TokenGuard
    {
        /** @var TokenGuard $guard */
        return $this->auth->guard('api-passport');
    }

    public function authenticate(string $username, string $password): ?string
    {
        /** @var User $user */
        $user = $this->guard()
            ->getProvider()
            ->retrieveByCredentials(['email' => $username, 'password' => $password]);

        if (
            !$user ||
            !$this->guard()
                ->getProvider()
                ->validateCredentials($user, ['email' => $username, 'password' => $password])
        ) {
            Log::warning('Intento de autenticación fallido (Passport).', [
                'email' => $username ?? null,
            ]);
            return null;
        }

        $tokenResult = $user->createToken('Personal Access Token');
        $accessToken = $tokenResult->accessToken;

        Log::info('Usuario autenticado correctamente (Passport).', [
            'user_id' => $user->id,
        ]);

        return $accessToken;
    }

    public function logout(): void
    {
        $user = $this->user();
        if (!$user) {
            return;
        }

        $token = $user->token();
        if ($token) {
            $token->revoke();
            Log::info('Sesión cerrada correctamente (Passport).', [
                'user_id' => $user->id,
            ]);
        }
    }

    public function refresh(): string
    {
        /** @var User|null $user */
        $user = $this->user();
        if (!$user) {
            throw new TokenRefreshException();
        }

        $exceptionErrorMessage = '';
        try {
            $user->tokens()->delete();
            $tokenResult = $user->createToken('Personal Access Token (refreshed)');

            Log::info('Token refrescado correctamente (Passport).', [
                'user_id' => $user->id,
            ]);

            return $tokenResult->accessToken;
        } catch (Throwable $e) {
            $exceptionErrorMessage = $e->getMessage();
            throw new TokenRefreshException($e);
        } finally {
            Log::error('Error durante el refresh de token (JWT).', [
                'error' => $exceptionErrorMessage,
            ]);
        }
    }

    public function getTTL(): int
    {
        return (int) config('passport.tokens_expire_in') / 60;
    }

    public function user(): ?User
    {
        return $this->guard()->user();
    }
}
