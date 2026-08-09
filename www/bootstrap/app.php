<?php

use App\Http\Middleware\ApplyLocalePreference;
use App\Http\Middleware\EnsureMfaIsVerified;
use App\Http\Middleware\RequestTelemetry;
use App\Http\Middleware\SecurityHeaders;
use App\Shared\Presentation\Exceptions\ApiExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(RequestTelemetry::class);
        $middleware->appendToGroup('web', ApplyLocalePreference::class);
        $middleware->appendToGroup('web', EnsureMfaIsVerified::class);
        $middleware->appendToGroup('api', ApplyLocalePreference::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->redirectGuestsTo(function ($request): string {
            if ($request->is('global-admin') || $request->is('global-admin/*')) {
                return route('global-admin.login');
            }

            return route('login');
        });
        $middleware->redirectUsersTo(function ($request): string {
            if ($request->is('global-admin') || $request->is('global-admin/*')) {
                return route('global-admin.home');
            }

            $user = $request->user();

            if ($user && (int) $user->current_company_id > 0) {
                return route('dashboard.industrial');
            }

            return route('onboarding.wizard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $handler = new ApiExceptionHandler;

        $exceptions->render(function (Throwable $exception, $request) use ($handler) {
            return $handler->render($request, $exception);
        });
    })->create();
