<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Exceptions;

use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    public function render(Request $request, Throwable $exception): Response
    {
        if ($exception instanceof AuthenticationException && ! $request->expectsJson()) {
            return redirect()->guest($exception->redirectTo($request) ?? route('login'));
        }

        if ($exception instanceof DomainException && ! $request->expectsJson()) {
            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation', 'current_password']))
                ->withErrors($this->normalizeDomainErrors($exception));
        }

        if ($exception instanceof ValidationException) {
            if (! $request->expectsJson()) {
                return redirect()->back()
                    ->withInput($request->except(['password', 'password_confirmation', 'current_password']))
                    ->withErrors($exception->errors());
            }

            return ApiResponse::error('Validation error', 422, $exception->errors(), 'VALIDATION_ERROR');
        }

        if (! $request->expectsJson()) {
            throw $exception;
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

    /**
     * @return array<string, array<int, string>>
     */
    private function normalizeDomainErrors(DomainException $exception): array
    {
        $details = $exception->details();

        if ($details === [] || array_is_list($details)) {
            return ['domain' => [$exception->getMessage()]];
        }

        $normalized = [];

        foreach ($details as $field => $messages) {
            if (is_array($messages)) {
                $normalized[(string) $field] = array_map(static fn ($message): string => (string) $message, $messages);

                continue;
            }

            $normalized[(string) $field] = [(string) $messages];
        }

        return $normalized !== [] ? $normalized : ['domain' => [$exception->getMessage()]];
    }
}
