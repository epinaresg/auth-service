<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\RefreshTokenController;
use App\Http\Middleware\AuthenticateWithAdapter;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->middleware(['api', SecurityHeaders::class])
    ->group(function () {
        Route::post('login', [LoginController::class, '__invoke'])
            ->middleware('throttle:login')
            ->name('auth.login');

        Route::post('logout', [LogoutController::class, '__invoke'])->name('auth.logout');

        Route::post('refresh', [RefreshTokenController::class, '__invoke'])->name('auth.refresh');

        Route::get('me', [MeController::class, '__invoke'])
            ->middleware(AuthenticateWithAdapter::class)
            ->name('auth.me');
    });
