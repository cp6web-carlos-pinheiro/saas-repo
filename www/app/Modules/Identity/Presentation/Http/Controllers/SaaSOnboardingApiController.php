<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Models\SaaS\EmailVerification;
use App\Models\SaaS\Organization;
use App\Models\SaaS\SocialAccount;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Services\SaaS\TrialOnboardingService;
use App\Support\Security\PasswordPolicy;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

final class SaaSOnboardingApiController
{
    public function register(Request $request, TrialOnboardingService $service): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'company' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email:rfc,dns', 'max:190'],
            'password' => ['required', 'confirmed', PasswordPolicy::rule()],
            'terms' => ['accepted'],
        ]);

        $result = $service->register($validated, $request);
        /** @var User $user */
        $user = $result['user'];

        $token = $user->createToken('api-trial-token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => $user,
            'organization' => $result['organization'],
            'trial' => $result['trial'],
        ], 'Trial iniciado com sucesso', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return ApiResponse::error('Credenciais invalidas', 401);
        }

        $token = $user->createToken('api-trial-token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 'Autenticado');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Sessao encerrada');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::broker('users')->sendResetLink([
            'email' => (string) $request->input('email'),
        ]);

        return ApiResponse::success(null, 'Se o e-mail existir, enviaremos instrucoes');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordPolicy::rule()],
        ]);

        $status = Password::broker('users')->reset(
            $payload,
            static function ($user, $password): void {
                $user->forceFill(['password' => $password])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return ApiResponse::error('Token invalido ou expirado', 422);
        }

        return ApiResponse::success(null, 'Senha redefinida com sucesso');
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $verification = EmailVerification::query()
            ->where('token', hash('sha256', $validated['token']))
            ->whereNull('verified_at')
            ->first();

        if (! $verification || $verification->expires_at->isPast()) {
            return ApiResponse::error('Token invalido ou expirado', 422);
        }

        $verification->user->forceFill(['email_verified_at' => now()])->save();
        $verification->update(['verified_at' => now()]);

        return ApiResponse::success(null, 'Email verificado com sucesso');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->email_verified_at !== null) {
            return ApiResponse::success(null, 'Email ja verificado');
        }

        $token = Str::random(64);

        EmailVerification::query()->create([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'expires_at' => now()->addDay(),
            'requested_ip' => $request->ip(),
        ]);

        return ApiResponse::success([
            'token' => $token,
        ], 'Token de verificacao reemitido');
    }

    public function socialLogin(Request $request, TrialOnboardingService $service): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'in:google,microsoft'],
            'access_token' => ['required', 'string'],
        ]);

        $provider = $validated['provider'];
        /** @var mixed $socialite */
        $socialite = Socialite::driver($provider);
        $oauthUser = $socialite->userFromToken($validated['access_token']);

        $email = (string) $oauthUser->getEmail();

        $link = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', (string) $oauthUser->getId())
            ->first();

        $user = $link?->user;

        if (! $user) {
            $user = User::query()->where('email', $email)->first();
        }

        if (! $user) {
            $name = (string) ($oauthUser->getName() ?: 'Novo Usuario');
            $companyHint = Str::title(Str::before($email, '@')).' Company';
            $result = $service->registerFromSocial($name, $companyHint, $email, $request);
            $user = $result['user'];
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        SocialAccount::query()->firstOrCreate([
            'provider' => $provider,
            'provider_user_id' => (string) $oauthUser->getId(),
        ], [
            'user_id' => $user->id,
            'email' => $email,
            'meta' => [
                'name' => $oauthUser->getName(),
                'avatar' => $oauthUser->getAvatar(),
            ],
        ]);

        $token = $user->createToken('api-trial-token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => $user,
        ], 'Autenticado via social login');
    }

    public function onboarding(Request $request, TrialOnboardingService $service): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'segment' => ['nullable', 'string', 'max:120'],
            'operation_size' => ['nullable', 'string', 'max:80'],
            'timezone' => ['required', 'timezone'],
            'import_data' => ['nullable', 'boolean'],
            'connect_integrations' => ['nullable', 'boolean'],
            'invite_team' => ['nullable', 'boolean'],
        ]);

        $profile = $service->completeOnboarding($user, $validated);

        return ApiResponse::success($profile, 'Onboarding atualizado');
    }

    public function trialStatus(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $organization = Organization::query()->where('company_id', $user->current_company_id)->first();

        if (! $organization) {
            return ApiResponse::error('Organizacao nao encontrada', 404);
        }

        $trial = Trial::query()
            ->where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (! $trial) {
            return ApiResponse::error('Trial nao encontrado', 404);
        }

        return ApiResponse::success([
            'status' => $trial->status,
            'is_expired' => $trial->is_expired,
            'trial_start_date' => $trial->trial_start_date,
            'trial_end_date' => $trial->trial_end_date,
            'days_remaining' => $trial->daysRemaining(),
            'organization_id' => $organization->id,
        ]);
    }

    public function tenantManagement(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $organization = Organization::query()->where('company_id', $user->current_company_id)->first();

        if (! $organization) {
            return ApiResponse::error('Organizacao nao encontrada', 404);
        }

        return ApiResponse::success([
            'organization' => $organization,
            'tenant' => $organization->tenants()->first(),
            'users' => $organization->company?->users()->paginate(15),
        ], 'Tenant management context');
    }

    public function createTenantUser(Request $request, TrialOnboardingService $service): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $service->isMasterUser($actor)) {
            return ApiResponse::error('Apenas usuario master pode cadastrar usuarios nesta conta', 403, null, 'FORBIDDEN');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc,dns', 'max:190'],
            'password' => ['required', 'confirmed', PasswordPolicy::rule()],
            'role' => ['nullable', 'in:member,master'],
            'activate' => ['nullable', 'boolean'],
        ]);

        try {
            $user = $service->registerTeamMember($actor, $validated, $request);
        } catch (ValidationException $exception) {
            return ApiResponse::error('Validation error', 422, $exception->errors(), 'VALIDATION_ERROR');
        }

        return ApiResponse::success([
            'user' => $user,
        ], 'Usuario cadastrado com sucesso', 201);
    }
}
