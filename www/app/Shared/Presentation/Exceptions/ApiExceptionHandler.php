<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Exceptions;

use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiExceptionHandler
{
    public function render(Request $request, Throwable $exception): Response
    {
        if ($exception instanceof AuthenticationException && ! $request->expectsJson()) {
            return redirect()->guest(route('login'));
        }

        if (! $request->expectsJson()) {
            throw $exception;
        }

        if ($exception instanceof ValidationException) {
            return ApiResponse::error('Validation error', 422, $exception->errors(), 'VALIDATION_ERROR');
        }

        if ($exception instanceof AuthenticationException) {
            return ApiResponse::error('Unauthenticated', 401, null, 'AUTH_REQUIRED');
        }

        if ($exception instanceof AuthorizationException) {
            return ApiResponse::error('Forbidden', 403, null, 'FORBIDDEN');
        }

        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return ApiResponse::error('Resource not found', 404, null, 'NOT_FOUND');
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return ApiResponse::error('Method not allowed', 405, null, 'METHOD_NOT_ALLOWED');
        }

        if ($exception instanceof DomainException) {
            return ApiResponse::error($exception->getMessage(), $exception->status(), $exception->details(), 'DOMAIN_ERROR');
        }

        report($exception);

        return ApiResponse::error(
            config('app.debug') ? $exception->getMessage() : 'Internal server error',
            500,
            null,
            'INTERNAL_ERROR'
        );
    }
}
