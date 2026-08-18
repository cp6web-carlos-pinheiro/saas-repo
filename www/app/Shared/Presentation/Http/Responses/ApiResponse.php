<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => self::requestMeta(),
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }

    public static function paginated(LengthAwarePaginator $paginator, string $message = 'OK'): JsonResponse
    {
        $request = request();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => self::requestMeta([
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
                'filters' => $request->input('filter', []),
                'sort' => [
                    'by' => $request->query('sort_by'),
                    'direction' => $request->query('sort_direction'),
                ],
            ]),
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public static function error(
        string $message,
        int $status = 400,
        ?array $errors = null,
        string $code = 'API_ERROR'
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'meta' => self::requestMeta(),
            'errors' => [
                [
                    'code' => $code,
                    'message' => $message,
                    'details' => $errors,
                ],
            ],
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }

    private static function requestMeta(array $extra = []): array
    {
        $request = request();

        $meta = [
            'api_version' => (string) ($request->segment(2) ?? 'v1'),
            'request_id' => (string) ($request->headers->get('X-Request-Id') ?? str()->uuid()),
        ];

        return array_merge($meta, $extra);
    }
}
