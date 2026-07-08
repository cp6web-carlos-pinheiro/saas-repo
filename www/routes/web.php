<?php

use App\Http\Controllers\Web\Auth\EmailVerificationController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\PasswordRecoveryController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Auth\SessionManagementController;
use App\Http\Controllers\Web\Auth\SocialAuthController;
use App\Http\Controllers\Web\Onboarding\OnboardingController;
use App\Http\Controllers\Web\Onboarding\TrialDashboardController;
use App\Http\Middleware\EnsureTrialIsActive;
use App\Http\Controllers\IndustrialDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/start-trial', [RegisterController::class, 'create'])->name('start-trial');
    Route::post('/start-trial', [RegisterController::class, 'store'])->name('start-trial.store');

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

Route::middleware('auth:web')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/sessions', [SessionManagementController::class, 'index'])->name('sessions.index');
    Route::delete('/sessions/{id}', [SessionManagementController::class, 'destroy'])->name('sessions.destroy');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.resend');

    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.wizard');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    Route::get('/trial/dashboard', TrialDashboardController::class)
        ->middleware(EnsureTrialIsActive::class)
        ->name('trial.dashboard');
});

Route::get('/dashboard', IndustrialDashboardController::class)->name('dashboard.industrial');
