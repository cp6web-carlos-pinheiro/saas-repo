<?php

use App\Http\Controllers\IndustrialDashboardController;
use App\Http\Controllers\Web\Admin\AdminAuthController;
use App\Http\Controllers\Web\Admin\GlobalAdminHomeController;
use App\Http\Controllers\Web\Admin\GlobalAdministratorController;
use App\Http\Controllers\Web\Admin\GlobalCompanyController;
use App\Http\Controllers\Web\Admin\GlobalCustomerController;
use App\Http\Controllers\Web\Admin\GlobalPlanController;
use App\Http\Controllers\Web\Auth\EmailVerificationController;
use App\Http\Controllers\Web\Auth\LanguagePreferenceController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\PasswordRecoveryController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Auth\SessionManagementController;
use App\Http\Controllers\Web\Auth\SocialAuthController;
use App\Http\Controllers\Web\Billing\SubscriptionController;
use App\Http\Controllers\Web\Documentation\DocumentationController;
use App\Http\Controllers\Web\Onboarding\AccountInvitationController;
use App\Http\Controllers\Web\Onboarding\OnboardingController;
use App\Http\Controllers\Web\Onboarding\PaymentController;
use App\Http\Controllers\Web\Tenant\CompanyAccessUserController;
use App\Http\Controllers\Web\Tenant\SupplierController;
use App\Http\Middleware\EnsureTrialIsActive;
use App\Http\Middleware\ResolveWebTenant;
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

Route::prefix('global-admin')->name('global-admin.')->middleware('guest:admin')->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
});

Route::post('/global-admin/logout', [AdminAuthController::class, 'destroy'])
    ->middleware('auth:admin')
    ->name('global-admin.logout');

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
    Route::get('/onboarding/payment/{planCode}', [PaymentController::class, 'create'])->name('onboarding.payment.create');
    Route::post('/onboarding/payment', [PaymentController::class, 'process'])->name('onboarding.payment.process');
    Route::get('/onboarding/payment/result', [PaymentController::class, 'result'])->name('onboarding.payment.result');

    Route::middleware(ResolveWebTenant::class)->group(function (): void {
        Route::get('/billing/subscription', [SubscriptionController::class, 'show'])
            ->name('billing.subscription.show');
        Route::post('/billing/subscription', [SubscriptionController::class, 'update'])
            ->name('billing.subscription.update');

        Route::redirect('/trial/dashboard', '/dashboard')
            ->middleware(EnsureTrialIsActive::class)
            ->name('trial.dashboard');

        Route::get('/dashboard', IndustrialDashboardController::class)
            ->middleware(EnsureTrialIsActive::class)
            ->name('dashboard.industrial');

        Route::prefix('company-access/users')->name('company-access.users.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/', [CompanyAccessUserController::class, 'index'])->name('index');
            Route::get('/create', [CompanyAccessUserController::class, 'create'])->name('create');
            Route::post('/', [CompanyAccessUserController::class, 'store'])->name('store');
            Route::get('/{customer}', [CompanyAccessUserController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CompanyAccessUserController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CompanyAccessUserController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CompanyAccessUserController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('purchasing/suppliers')->name('purchasing.suppliers.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
        });
    });

});

Route::prefix('global-admin')->name('global-admin.')->middleware('auth:admin')->group(function (): void {
    Route::get('/', GlobalAdminHomeController::class)->name('home');
    Route::get('/customers', [GlobalCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [GlobalCustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [GlobalCustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [GlobalCustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [GlobalCustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [GlobalCustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [GlobalCustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/companies', [GlobalCompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [GlobalCompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [GlobalCompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}', [GlobalCompanyController::class, 'show'])->name('companies.show');
    Route::get('/companies/{company}/edit', [GlobalCompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [GlobalCompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [GlobalCompanyController::class, 'destroy'])->name('companies.destroy');

    Route::get('/plans', [GlobalPlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/create', [GlobalPlanController::class, 'create'])->name('plans.create');
    Route::post('/plans', [GlobalPlanController::class, 'store'])->name('plans.store');
    Route::get('/plans/{plan}', [GlobalPlanController::class, 'show'])->name('plans.show');
    Route::get('/plans/{plan}/edit', [GlobalPlanController::class, 'edit'])->name('plans.edit');
    Route::put('/plans/{plan}', [GlobalPlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}', [GlobalPlanController::class, 'destroy'])->name('plans.destroy');

    Route::get('/administrators', [GlobalAdministratorController::class, 'index'])->name('administrators.index');
    Route::get('/administrators/create', [GlobalAdministratorController::class, 'create'])->name('administrators.create');
    Route::post('/administrators', [GlobalAdministratorController::class, 'store'])->name('administrators.store');
    Route::get('/administrators/{administrator}', [GlobalAdministratorController::class, 'show'])->name('administrators.show');
    Route::get('/administrators/{administrator}/edit', [GlobalAdministratorController::class, 'edit'])->name('administrators.edit');
    Route::put('/administrators/{administrator}', [GlobalAdministratorController::class, 'update'])->name('administrators.update');
    Route::delete('/administrators/{administrator}', [GlobalAdministratorController::class, 'destroy'])->name('administrators.destroy');
});
