<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ── PCI DSS REQ 1, 2, 4, 6, 8 ────────────────────────────
        // Apply security headers, session timeout, and HTTPS enforcement
        // to EVERY web request automatically
        $middleware->web(append: [
            \App\Http\Middleware\PciSecurityMiddleware::class,
        ]);

        // ── PCI DSS REQ 7 ────────────────────────────────────────
        // Register the 'admin' middleware alias.
        // This lets us write: ->middleware(['admin']) in routes/web.php
        // to protect admin-only pages.
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();