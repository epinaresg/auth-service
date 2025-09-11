<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\RefreshTokenController;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->middleware(['api', SecurityHeaders::class])
    ->group(function () {
        Route::post('login', LoginController::class)->middleware('throttle:login')->name('auth.login');
        Route::post('logout', LogoutController::class)->name('auth.logout');
        Route::post('refresh', RefreshTokenController::class)->name('auth.refresh');
        Route::get('me', MeController::class)->middleware('jwt.auth')->name('auth.me');
    });
