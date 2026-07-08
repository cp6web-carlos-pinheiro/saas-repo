<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

final class AuthController
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'current_company_id' => $user->current_company_id,
            ],
        ], 'Authenticated');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logged out');
    }

    public function loginJwt(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $token = JWTAuth::attempt($credentials);

        if (! $token) {
            return ApiResponse::error('Invalid credentials', 401, null, 'AUTH_INVALID_CREDENTIALS');
        }

        /** @var User $user */
        $user = JWTAuth::user();

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => (int) config('jwt.ttl', 60) * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'current_company_id' => $user->current_company_id,
            ],
        ], 'Authenticated with JWT');
    }

    public function meJwt(): JsonResponse
    {
        return ApiResponse::success(JWTAuth::user(), 'Authenticated JWT user');
    }

    public function refreshJwt(): JsonResponse
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => (int) config('jwt.ttl', 60) * 60,
        ], 'JWT token refreshed');
    }

    public function logoutJwt(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return ApiResponse::success(null, 'JWT token invalidated');
    }
}
