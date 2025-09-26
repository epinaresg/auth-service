<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__ . '/../routes/web.php', api: __DIR__ . '/../routes/api.php', commands: __DIR__ . '/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
            return $request->expectsJson();
        });

        $exceptions->respond(function (Response $response, Throwable $e) {
            $status = $response->getStatusCode();
            $status = $status >= 100 && $status < 600 ? $status : Response::HTTP_INTERNAL_SERVER_ERROR;

            $responseData = [
                'success' => false,
            ];

            if ($status == Response::HTTP_INTERNAL_SERVER_ERROR) {
                $responseData['message'] = 'Unexpected error.';

                if (env('APP_ENV') !== 'production') {
                    $responseData['debug'] = json_decode($response->getContent());
                }
            } else {
                $responseData = array_merge($responseData, (array) json_decode($response->getContent(), true));
            }

            return response()->json($responseData, $status);
        });
    })
    ->booting(function () {
        Passport::tokensExpireIn(now()->addSeconds(config('passport.tokens_expire_in')));
        Passport::refreshTokensExpireIn(now()->addSeconds(config('passport.refresh_tokens_expire_in')));
    })
    ->create();
