<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
        'otp.verified' => \App\Http\Middleware\EnsureOtpVerified::class,
        'check.otp' => \App\Http\Middleware\CheckOtp::class,
         $middleware->redirectGuestsTo(fn () => route('login.show')),
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
