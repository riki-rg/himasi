<?php

use App\Http\Middleware\EnsureEmailIsVerified;
use App\Support\Problem;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => EnsureEmailIsVerified::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $problem = match (true) {
                $e instanceof Problem => $e,
                $e instanceof ValidationException => Problem::validation($e->errors()),
                $e instanceof AuthenticationException => Problem::unauthorized(),
                $e instanceof AccessDeniedHttpException => Problem::forbidden(),
                $e instanceof NotFoundHttpException => Problem::notFound(),
                $e instanceof ModelNotFoundException => Problem::notFound(),
                default => null,
            };

            if ($problem === null) {
                return null;
            }

            return response()
                ->json($problem->toArray(), $problem->statusCode)
                ->header('Content-Type', 'application/problem+json');
        });
    })->create();
