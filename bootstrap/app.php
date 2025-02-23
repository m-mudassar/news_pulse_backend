<?php

use App\Helpers\ResponseHelper;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return ResponseHelper::error('Unauthenticated', [], 401);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return ResponseHelper::error('Validation Error', $e->errors(), 422);
        });

        $exceptions->render(function (RouteNotFoundException $e, Request $request) {
            if (str_contains($e->getMessage(), 'login')) {
                return ResponseHelper::error('Unauthorized', [], 401);
            }

            return ResponseHelper::error('Endpoint not found.', [], 404);
        });
    })->create();
