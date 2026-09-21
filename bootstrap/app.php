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
        // Replace default CSRF middleware with patched version to fix Livewire Redirector compatibility.
        // @see app/Http/Middleware/VerifyCsrfToken.php
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        );

        $middleware->use([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        // Add middleware aliases for better organization
        $middleware->alias([
            'filament.auth' => \Filament\Http\Middleware\Authenticate::class,
            'check.expiration' => \App\Http\Middleware\CheckUserExpiration::class,
            'project.access' => \App\Http\Middleware\CheckProjectAccess::class,
            'no-store' => \App\Http\Middleware\NoStoreResponse::class,
            'super-admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'app.license' => \App\Http\Middleware\EnsureAppLicense::class,
            'subscription.agreement' => \App\Http\Middleware\EnsureSubscriptionAgreementAccepted::class,
            'public-form' => \App\Http\Middleware\PublicFormSecurityHeaders::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'api.account.active' => \App\Http\Middleware\EnsureApiAccountActive::class,
            'api.app.license' => \App\Http\Middleware\EnsureApiAppLicense::class,
            'pro.feature' => \App\Http\Middleware\EnsureProFeature::class,
        ]);

        // Ensure proper web middleware group for Niaga Hoster
        $middleware->web(append: [
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        // Apply CheckUserExpiration to web routes
        $middleware->web(\App\Http\Middleware\CheckUserExpiration::class);

        // Handle method spoofing properly
        $middleware->web(prepend: [
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return response()->redirectTo(config('app.url'));
        });

        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Method Not Allowed',
                    'message' => 'The requested method is not allowed for this route.',
                    'allowed_methods' => $e->getHeaders()['Allow'] ?? 'GET, POST',
                ], 405);
            }
            return response()->redirectToRoute('home')->with('error', 'Method tidak diizinkan untuk halaman ini.');
        });

        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Not Found',
                    'message' => 'The requested resource was not found.',
                ], 404);
            }
            return response()->redirectToRoute('home')->with('error', 'Halaman tidak ditemukan.');
        });
    })->create();
