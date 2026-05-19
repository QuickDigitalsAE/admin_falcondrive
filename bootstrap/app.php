<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middlewares
        $middleware->alias([
            'check.authorization' => \App\Http\Middleware\CheckAuthorization::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'active.user' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Register middleware groups:
        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            // Agar request API call hai to JSON response dena hai
            if ($request->expectsJson() || $request->is('api/*')) {
                $status = 500;
                $message = 'Something went wrong';

                if (method_exists($e, 'getStatusCode')) {
                    $status = $e->getStatusCode();
                }

                // Customize specific exceptions if you want
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $status = 422;
                    $message = $e->validator->errors()->first();
                } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    $status = 404;
                    $message = 'Resource not found.';
                } elseif ($e instanceof \Illuminate\Database\QueryException) {
                    $status = 422;
                    $sqlMessage = $e->getMessage();

                    if (str_contains($sqlMessage, 'Base table or view not found')) {
                        $message = 'Database table is missing: ' . $sqlMessage;
                    } elseif (str_contains($sqlMessage, 'foreign key constraint fails')) {
                        $message = 'Invalid reference: related record does not exist. ' . $sqlMessage;
                    } else {
                        $message = 'A database error occurred. ' . $sqlMessage;
                    }
                } elseif ($e->getMessage() == 'Route [login] not defined.') {
                    $status = 401;
                    $message = 'You are not authorized. Please log in.';
                } else {
                    $message = $e->getMessage() ?: $message;
                }

                return new JsonResponse([
                    'success' => false,
                    'message' => $message,
                ], $status);
            }

            // For non-API requests, fallback to default Laravel error handling
            return null;
        });
    })->create();
