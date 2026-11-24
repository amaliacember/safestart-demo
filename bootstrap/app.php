<?php

use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    // No health route (/up): the demo only exposes the routes in routes/web.php
    // (plus the ones Livewire itself needs).
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // `herd share` puts an Expose tunnel in front of Herd. The tunnel client
        // talks to Herd from this same machine, so we trust proxy headers from
        // loopback only, and only the protocol/host/port headers. That way
        // Laravel sees the request as HTTPS (secure cookies, https:// URLs).
        // X-Forwarded-For is deliberately NOT trusted: a visitor could put a
        // fake IP in it to dodge the rate limiter. So everyone coming through
        // the tunnel has the same IP; the "demo" rate limiter therefore counts
        // per session first (see AppServiceProvider).
        $middleware->trustProxies(
            at: ['127.0.0.1', '::1'],
            headers: Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT,
        );

        // Appended to the "web" group so they run after the session has started.
        // Livewire's update requests use the same group, so they get the same
        // language and headers.
        $middleware->web(append: [
            SetLocale::class,
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
