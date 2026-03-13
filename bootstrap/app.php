<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role'            => \App\Http\Middleware\CheckRole::class,
            'activity.active' => \App\Http\Middleware\EnsureActivityActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
         /*
         * Intercept HTTP error responses and render them via Inertia so that
         * the Vue Error.vue page is shown with the application's full theme.
         *
         * How this works:
         *  - `respond` fires for every outgoing HTTP response before it is sent,
         *    letting us transform it transparently.
         *  - We skip pure JSON/API requests (Accept: application/json) so those
         *    consumers still receive structured JSON error payloads.
         *  - `Inertia::render('Error', [...])` detects the request type itself:
         *    full page load → full HTML shell (app.blade.php + Vite assets);
         *    Inertia XHR navigation → JSON component diff.
         *  - `->setStatusCode()` preserves the correct HTTP status so that
         *    browsers, crawlers, and monitoring tools see the right code.
         */
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $status       = $response->getStatusCode();
            $handledCodes = [403, 404, 419, 429, 500, 503];

            if (! $request->expectsJson() && in_array($status, $handledCodes)) {
                return Inertia::render('Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
