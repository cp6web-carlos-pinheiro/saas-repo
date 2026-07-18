<?php

use App\Http\Controllers\Web\Auth\EmailVerificationController;
use App\Http\Controllers\Web\Auth\LanguagePreferenceController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\PasswordRecoveryController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Auth\SessionManagementController;
use App\Http\Controllers\Web\Auth\SocialAuthController;
use App\Http\Controllers\Web\Admin\AdminManagementController;
use App\Http\Controllers\Web\Onboarding\AccountInvitationController;
use App\Http\Controllers\Web\Documentation\DocumentationController;
use App\Http\Controllers\Web\Onboarding\OnboardingController;
use App\Http\Middleware\EnsureTrialIsActive;
use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\ResolveWebTenant;
use App\Http\Controllers\IndustrialDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/locale', [LanguagePreferenceController::class, 'update'])->name('locale.update');

Route::middleware('guest')->group(function (): void {
    Route::get('/start-trial', [RegisterController::class, 'create'])->name('start-trial');
    Route::post('/start-trial', [RegisterController::class, 'store'])->name('start-trial.store');
    Route::get('/cadastro-empresa', [RegisterController::class, 'create'])->name('company-signup');
    Route::post('/cadastro-empresa', [RegisterController::class, 'store'])->name('company-signup.store');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/forgot-password', [PasswordRecoveryController::class, 'forgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordRecoveryController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordRecoveryController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordRecoveryController::class, 'reset'])->name('password.update');

    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
});

Route::get('/email/verify/token/{token}', [EmailVerificationController::class, 'verifyToken'])->name('verification.verify-token');
Route::get('/convite/{token}', [AccountInvitationController::class, 'show'])->name('account-invitations.show');
Route::post('/convite/{token}', [AccountInvitationController::class, 'accept'])->name('account-invitations.accept');

Route::middleware('auth:web')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::post('/preferences/language', [LanguagePreferenceController::class, 'update'])->name('preferences.language.update');
    Route::get('/docs', [DocumentationController::class, 'index'])->name('docs.index');
    Route::get('/docs/{file}', [DocumentationController::class, 'show'])
        ->where('file', '[^/]+')
        ->name('docs.show');
    Route::get('/docs/dev/{file}', [DocumentationController::class, 'showDev'])
        ->where('file', '[^/]+')
        ->name('docs.dev.show');

    Route::get('/sessions', [SessionManagementController::class, 'index'])->name('sessions.index');
    Route::delete('/sessions/{id}', [SessionManagementController::class, 'destroy'])->name('sessions.destroy');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.resend');

    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.wizard');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    Route::middleware(ResolveWebTenant::class)->group(function (): void {
        Route::redirect('/trial/dashboard', '/dashboard')
            ->middleware(EnsureTrialIsActive::class)
            ->name('trial.dashboard');

        Route::get('/dashboard', IndustrialDashboardController::class)
            ->middleware(EnsureTrialIsActive::class)
            ->name('dashboard.industrial');
    });

    Route::prefix('admin')->name('admin.')->middleware(EnsurePlatformAdmin::class)->group(function (): void {
        Route::get('/', [AdminManagementController::class, 'index'])->name('management');

        Route::patch('/companies/{company}/status', [AdminManagementController::class, 'updateCompanyStatus'])
            ->name('companies.status');
        Route::patch('/companies/{company}/plan', [AdminManagementController::class, 'updateCompanyPlan'])
            ->name('companies.plan');

        Route::patch('/users/{user}/status', [AdminManagementController::class, 'updateUserStatus'])
            ->name('users.status');
        Route::patch('/users/{user}/email-verification', [AdminManagementController::class, 'updateUserEmailVerification'])
            ->name('users.email-verification');
        Route::patch('/users/{user}/platform-admin', [AdminManagementController::class, 'updatePlatformAdmin'])
            ->name('users.platform-admin');
    });
});
