<?php

use App\Http\Controllers\IndustrialDashboardController;
use App\Http\Controllers\Web\Admin\AdminAuthController;
use App\Http\Controllers\Web\Admin\GlobalAdminHomeController;
use App\Http\Controllers\Web\Admin\GlobalAdministratorController;
use App\Http\Controllers\Web\Admin\GlobalCompanyController;
use App\Http\Controllers\Web\Admin\GlobalCustomerController;
use App\Http\Controllers\Web\Admin\GlobalPageTutorialController;
use App\Http\Controllers\Web\Admin\GlobalPlanController;
use App\Http\Controllers\Web\Auth\EmailVerificationController;
use App\Http\Controllers\Web\Auth\LanguagePreferenceController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\MfaChallengeController;
use App\Http\Controllers\Web\Auth\PasswordRecoveryController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Auth\SessionManagementController;
use App\Http\Controllers\Web\Auth\SocialAuthController;
use App\Http\Controllers\Web\Billing\SubscriptionController;
use App\Http\Controllers\Web\Documentation\DocumentationController;
use App\Http\Controllers\Web\Onboarding\AccountInvitationController;
use App\Http\Controllers\Web\Onboarding\OnboardingController;
use App\Http\Controllers\Web\Onboarding\PaymentController;
use App\Http\Controllers\Web\Tenant\AdminData\BrandsController;
use App\Http\Controllers\Web\Tenant\AdminData\CategoriesController;
use App\Http\Controllers\Web\Tenant\AdminData\UnitsController;
use App\Http\Controllers\Web\Tenant\BomMaterialListController;
use App\Http\Controllers\Web\Tenant\BomStructureController;
use App\Http\Controllers\Web\Tenant\CompanyAccessUserController;
use App\Http\Controllers\Web\Tenant\CustomerController;
use App\Http\Controllers\Web\Tenant\DomainDashboardController;
use App\Http\Controllers\Web\Tenant\InventoryWebController;
use App\Http\Controllers\Web\Tenant\PageTutorialController;
use App\Http\Controllers\Web\Tenant\PlantController;
use App\Http\Controllers\Web\Tenant\ProductController;
use App\Http\Controllers\Web\Tenant\ProductionAnalyticsController;
use App\Http\Controllers\Web\Tenant\ProductionCalendarWebController;
use App\Http\Controllers\Web\Tenant\ProductionOrderController;
use App\Http\Controllers\Web\Tenant\ProductionRoutingController;
use App\Http\Controllers\Web\Tenant\ProductionSchedulingWebController;
use App\Http\Controllers\Web\Tenant\ProductionWorkCenterController;
use App\Http\Controllers\Web\Tenant\ProductVersionController;
use App\Http\Controllers\Web\Tenant\PurchaseOrderController;
use App\Http\Controllers\Web\Tenant\PurchaseQuotationController;
use App\Http\Controllers\Web\Tenant\PurchaseReceiptController;
use App\Http\Controllers\Web\Tenant\PurchaseRequisitionController;
use App\Http\Controllers\Web\Tenant\PurchasingLookupController;
use App\Http\Controllers\Web\Tenant\RbacConsoleController;
use App\Http\Controllers\Web\Tenant\SaleController;
use App\Http\Controllers\Web\Tenant\SupplierController;
use App\Http\Controllers\Web\Tenant\WarehouseController;
use App\Http\Middleware\EnsureTrialIsActive;
use App\Http\Middleware\ResolveWebTenant;
use App\Modules\Identity\Presentation\Http\Middleware\CheckPermission;
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
    Route::get('/mfa/challenge', [MfaChallengeController::class, 'create'])->name('mfa.challenge');
    Route::post('/mfa/challenge', [MfaChallengeController::class, 'store'])->name('mfa.challenge.store');
    Route::post('/mfa/challenge/resend', [MfaChallengeController::class, 'resend'])->name('mfa.challenge.resend');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::post('/preferences/language', [LanguagePreferenceController::class, 'update'])->name('preferences.language.update');

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
        Route::post('/page-tutorials', [PageTutorialController::class, 'upsert'])
            ->name('page-tutorials.upsert');

        Route::get('/billing/subscription', [SubscriptionController::class, 'show'])
            ->middleware(CheckPermission::class.':company-access.billing.read')
            ->name('billing.subscription.show');
        Route::post('/billing/subscription', [SubscriptionController::class, 'update'])
            ->middleware(CheckPermission::class.':company-access.billing.update')
            ->name('billing.subscription.update');

        Route::redirect('/trial/dashboard', '/dashboard')
            ->middleware(EnsureTrialIsActive::class)
            ->name('trial.dashboard');

        Route::get('/dashboard', IndustrialDashboardController::class)
            ->middleware([EnsureTrialIsActive::class, CheckPermission::class.':company-access.dashboard.read'])
            ->name('dashboard.industrial');

        Route::get('/domains/{domain}/dashboard', DomainDashboardController::class)
            ->whereIn('domain', ['engineering', 'planning', 'shop_floor', 'analysis', 'inventory', 'purchasing', 'sales', 'administration'])
            ->middleware(EnsureTrialIsActive::class)
            ->name('domains.dashboard');

        Route::prefix('company-access/users')->name('company-access.users.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/', [CompanyAccessUserController::class, 'index'])->name('index');
            Route::get('/create', [CompanyAccessUserController::class, 'create'])->name('create');
            Route::post('/', [CompanyAccessUserController::class, 'store'])->name('store');
            Route::get('/{customer}', [CompanyAccessUserController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CompanyAccessUserController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CompanyAccessUserController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CompanyAccessUserController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('company-access/rbac')->name('company-access.rbac.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/', static fn () => redirect()->route('company-access.rbac.roles.index'))->name('index');
            Route::get('/roles', [RbacConsoleController::class, 'roles'])->name('roles.index');

            Route::get('/roles/create', [RbacConsoleController::class, 'createRole'])->name('roles.create');
            Route::post('/roles', [RbacConsoleController::class, 'storeRole'])->name('roles.store');
            Route::get('/roles/{role}', [RbacConsoleController::class, 'showRole'])->name('roles.show');
            Route::get('/roles/{role}/edit', [RbacConsoleController::class, 'editRole'])->name('roles.edit');
            Route::put('/roles/{role}', [RbacConsoleController::class, 'updateRole'])->name('roles.update');
            Route::delete('/roles/{role}', [RbacConsoleController::class, 'destroyRole'])->name('roles.destroy');
        });

        Route::prefix('suppliers')->name('purchasing.suppliers.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('purchasing/requisitions')->name('purchasing.requisitions.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/lookup', [PurchasingLookupController::class, 'requisitions'])->name('lookup');
            Route::get('/', [PurchaseRequisitionController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseRequisitionController::class, 'create'])->name('create');
            Route::post('/', [PurchaseRequisitionController::class, 'store'])->name('store');
            Route::get('/{requisition}', [PurchaseRequisitionController::class, 'show'])->name('show');
            Route::get('/{requisition}/edit', [PurchaseRequisitionController::class, 'edit'])->name('edit');
            Route::put('/{requisition}', [PurchaseRequisitionController::class, 'update'])->name('update');
            Route::delete('/{requisition}', [PurchaseRequisitionController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('purchasing/quotations')->name('purchasing.quotations.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/', [PurchaseQuotationController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseQuotationController::class, 'create'])->name('create');
            Route::post('/', [PurchaseQuotationController::class, 'store'])->name('store');
            Route::get('/{quotation}', [PurchaseQuotationController::class, 'show'])->name('show');
            Route::get('/{quotation}/edit', [PurchaseQuotationController::class, 'edit'])->name('edit');
            Route::put('/{quotation}', [PurchaseQuotationController::class, 'update'])->name('update');
            Route::delete('/{quotation}', [PurchaseQuotationController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('purchasing/orders')->name('purchasing.orders.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/lookup', [PurchasingLookupController::class, 'orders'])->name('lookup');
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
            Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
            Route::get('/{order}', [PurchaseOrderController::class, 'show'])->name('show');
            Route::get('/{order}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
            Route::put('/{order}', [PurchaseOrderController::class, 'update'])->name('update');
            Route::delete('/{order}', [PurchaseOrderController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('purchasing/receipts')->name('purchasing.receipts.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/', [PurchaseReceiptController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseReceiptController::class, 'create'])->name('create');
            Route::post('/', [PurchaseReceiptController::class, 'store'])->name('store');
            Route::get('/{receipt}', [PurchaseReceiptController::class, 'show'])->name('show');
            Route::get('/{receipt}/edit', [PurchaseReceiptController::class, 'edit'])->name('edit');
            Route::post('/{receipt}/reverse', [PurchaseReceiptController::class, 'reverse'])->name('reverse');
            Route::put('/{receipt}', [PurchaseReceiptController::class, 'update'])->name('update');
            Route::delete('/{receipt}', [PurchaseReceiptController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('purchasing/lookups')->name('purchasing.lookups.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/suppliers', [PurchasingLookupController::class, 'suppliers'])->name('suppliers');
            Route::get('/requisitions', [PurchasingLookupController::class, 'requisitions'])->name('requisitions');
            Route::get('/orders', [PurchasingLookupController::class, 'orders'])->name('orders');
            Route::get('/products', [PurchasingLookupController::class, 'products'])->name('products');
            Route::get('/warehouses', [PurchasingLookupController::class, 'warehouses'])->name('warehouses');
            Route::get('/order-lines', [PurchasingLookupController::class, 'orderLines'])->name('order-lines');
        });

        Route::prefix('inventory/warehouses')->name('inventory.warehouses.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/', [WarehouseController::class, 'index'])->name('index');
            Route::get('/create', [WarehouseController::class, 'create'])->name('create');
            Route::post('/', [WarehouseController::class, 'store'])->name('store');
            Route::get('/{warehouse}', [WarehouseController::class, 'show'])->name('show');
            Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
            Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
            Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('inventory')->name('inventory.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/balances', [InventoryWebController::class, 'balances'])->name('balances.index');
            Route::get('/movements', [InventoryWebController::class, 'movements'])->name('movements.index');
            Route::get('/movements/create', [InventoryWebController::class, 'createMovement'])->name('movements.create');
            Route::post('/movements', [InventoryWebController::class, 'storeMovement'])->name('movements.store');
        });

        Route::prefix('admin-data')->name('admin-data.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::prefix('units')->name('units.')->group(function (): void {
                Route::get('/', [UnitsController::class, 'index'])->name('index');
                Route::get('/create', [UnitsController::class, 'create'])->name('create');
                Route::post('/', [UnitsController::class, 'store'])->name('store');
                Route::get('/{unit}', [UnitsController::class, 'show'])->name('show');
                Route::get('/{unit}/edit', [UnitsController::class, 'edit'])->name('edit');
                Route::put('/{unit}', [UnitsController::class, 'update'])->name('update');
                Route::delete('/{unit}', [UnitsController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('categories')->name('categories.')->group(function (): void {
                Route::get('/', [CategoriesController::class, 'index'])->name('index');
                Route::get('/create', [CategoriesController::class, 'create'])->name('create');
                Route::post('/', [CategoriesController::class, 'store'])->name('store');
                Route::get('/{category}', [CategoriesController::class, 'show'])->name('show');
                Route::get('/{category}/edit', [CategoriesController::class, 'edit'])->name('edit');
                Route::put('/{category}', [CategoriesController::class, 'update'])->name('update');
                Route::delete('/{category}', [CategoriesController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('brands')->name('brands.')->group(function (): void {
                Route::get('/', [BrandsController::class, 'index'])->name('index');
                Route::get('/create', [BrandsController::class, 'create'])->name('create');
                Route::post('/', [BrandsController::class, 'store'])->name('store');
                Route::get('/{brand}', [BrandsController::class, 'show'])->name('show');
                Route::get('/{brand}/edit', [BrandsController::class, 'edit'])->name('edit');
                Route::put('/{brand}', [BrandsController::class, 'update'])->name('update');
                Route::delete('/{brand}', [BrandsController::class, 'destroy'])->name('destroy');
            });
        });

        Route::prefix('inventory/plants')->name('inventory.plants.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/', [PlantController::class, 'index'])->name('index');
            Route::get('/create', [PlantController::class, 'create'])->name('create');
            Route::post('/', [PlantController::class, 'store'])->name('store');
            Route::get('/{plant}', [PlantController::class, 'show'])->name('show');
            Route::get('/{plant}/edit', [PlantController::class, 'edit'])->name('edit');
            Route::put('/{plant}', [PlantController::class, 'update'])->name('update');
            Route::delete('/{plant}', [PlantController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('customers')->name('customers.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('sales')->name('sales.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/', [SaleController::class, 'index'])->name('index');
            Route::get('/products/search', [SaleController::class, 'searchProducts'])->name('products.search');
            Route::get('/create', [SaleController::class, 'create'])->name('create');
            Route::post('/', [SaleController::class, 'store'])->name('store');
            Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
            Route::get('/{sale}/edit', [SaleController::class, 'edit'])->name('edit');
            Route::post('/{sale}/transition', [SaleController::class, 'transition'])->name('transition');
            Route::put('/{sale}', [SaleController::class, 'update'])->name('update');
            Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('production')->name('production.')->middleware(EnsureTrialIsActive::class)->group(function (): void {
            Route::get('/products/search', [ProductionOrderController::class, 'searchProducts'])->name('products.search');

            Route::get('/orders', [ProductionOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/create', [ProductionOrderController::class, 'create'])->name('orders.create');
            Route::post('/orders', [ProductionOrderController::class, 'store'])->name('orders.store');
            Route::get('/orders/{order}', [ProductionOrderController::class, 'show'])->whereNumber('order')->name('orders.show');
            Route::post('/orders/{order}/release', [ProductionOrderController::class, 'release'])->whereNumber('order')->name('orders.release');
            Route::post('/orders/{order}/complete', [ProductionOrderController::class, 'complete'])->whereNumber('order')->name('orders.complete');
            Route::post('/orders/{order}/outputs', [ProductionOrderController::class, 'recordOutput'])->whereNumber('order')->name('orders.outputs.store');
            Route::post('/orders/{order}/consumptions', [ProductionOrderController::class, 'recordConsumption'])->whereNumber('order')->name('orders.consumptions.store');
            Route::post('/orders/{order}/outputs/{output}/inspection', [ProductionOrderController::class, 'updateInspection'])
                ->whereNumber('order')
                ->whereNumber('output')
                ->name('orders.outputs.inspection.update');

            Route::get('/analytics', [ProductionAnalyticsController::class, 'index'])->name('analytics.index');

            Route::get('/routing', [ProductionRoutingController::class, 'index'])->name('routing.index');
            Route::get('/routing/create', [ProductionRoutingController::class, 'create'])->name('routing.create');
            Route::post('/routing', [ProductionRoutingController::class, 'store'])->name('routing.store');
            Route::get('/routing/{version}', [ProductionRoutingController::class, 'show'])->whereNumber('version')->name('routing.show');
            Route::get('/routing/{version}/edit', [ProductionRoutingController::class, 'edit'])->whereNumber('version')->name('routing.edit');
            Route::put('/routing/{version}', [ProductionRoutingController::class, 'update'])->whereNumber('version')->name('routing.update');
            Route::post('/routing/{version}/operations', [ProductionRoutingController::class, 'storeOperation'])->whereNumber('version')->name('routing.operations.store');
            Route::post('/routing/{version}/approve', [ProductionRoutingController::class, 'approve'])->whereNumber('version')->name('routing.approve');

            Route::get('/work-centers', [ProductionWorkCenterController::class, 'index'])->name('work-centers.index');
            Route::get('/work-centers/create', [ProductionWorkCenterController::class, 'create'])->name('work-centers.create');
            Route::post('/work-centers', [ProductionWorkCenterController::class, 'store'])->name('work-centers.store');
            Route::get('/work-centers/{workCenter}', [ProductionWorkCenterController::class, 'show'])->whereNumber('workCenter')->name('work-centers.show');
            Route::get('/work-centers/{workCenter}/edit', [ProductionWorkCenterController::class, 'edit'])->whereNumber('workCenter')->name('work-centers.edit');
            Route::put('/work-centers/{workCenter}', [ProductionWorkCenterController::class, 'update'])->whereNumber('workCenter')->name('work-centers.update');
            Route::post('/work-centers/{workCenter}/shifts', [ProductionWorkCenterController::class, 'storeShift'])->whereNumber('workCenter')->name('work-centers.shifts.store');

            Route::get('/calendar', [ProductionCalendarWebController::class, 'index'])->name('calendar.index');
            Route::get('/calendar/create', [ProductionCalendarWebController::class, 'create'])->name('calendar.create');
            Route::post('/calendar', [ProductionCalendarWebController::class, 'store'])->name('calendar.store');
            Route::get('/calendar/{day}', [ProductionCalendarWebController::class, 'show'])->whereNumber('day')->name('calendar.show');
            Route::get('/calendar/{day}/edit', [ProductionCalendarWebController::class, 'edit'])->whereNumber('day')->name('calendar.edit');
            Route::put('/calendar/{day}', [ProductionCalendarWebController::class, 'update'])->whereNumber('day')->name('calendar.update');
            Route::post('/calendar/day', [ProductionCalendarWebController::class, 'upsertDay'])->name('calendar.days.upsert');
            Route::post('/calendar/generate', [ProductionCalendarWebController::class, 'generate'])->name('calendar.generate');

            Route::get('/scheduling', [ProductionSchedulingWebController::class, 'index'])->name('scheduling.index');
            Route::get('/scheduling/create', [ProductionSchedulingWebController::class, 'create'])->name('scheduling.create');
            Route::post('/scheduling/run', [ProductionSchedulingWebController::class, 'run'])->name('scheduling.run');
            Route::get('/scheduling/{run}', [ProductionSchedulingWebController::class, 'show'])->name('scheduling.show');
            Route::get('/scheduling/{run}/edit', [ProductionSchedulingWebController::class, 'edit'])->name('scheduling.edit');
            Route::put('/scheduling/{run}', [ProductionSchedulingWebController::class, 'update'])->name('scheduling.update');
        });

        Route::middleware([EnsureTrialIsActive::class, CheckPermission::class.':bom.explode'])->group(function (): void {
            Route::prefix('bom/structures')->name('bom.structures.')->group(function (): void {
                Route::get('/', [BomStructureController::class, 'index'])->name('index');
                Route::get('/{product}', [BomStructureController::class, 'show'])->whereNumber('product')->name('show');
            });

            Route::prefix('bom/material-lists')->name('bom.material-lists.')->group(function (): void {
                Route::get('/', [BomMaterialListController::class, 'index'])->name('index');
                Route::get('/create', [BomMaterialListController::class, 'create'])->name('create');
                Route::post('/', [BomMaterialListController::class, 'store'])->name('store');
                Route::get('/component-products/{product}/unit', [BomMaterialListController::class, 'componentProductUnit'])
                    ->whereNumber('product')
                    ->name('component-products.unit');
                Route::get('/{bom}', [BomMaterialListController::class, 'show'])->whereNumber('bom')->name('show');
                Route::get('/{bom}/edit', [BomMaterialListController::class, 'edit'])->whereNumber('bom')->name('edit');
                Route::put('/{bom}', [BomMaterialListController::class, 'update'])->whereNumber('bom')->name('update');
                Route::delete('/{bom}', [BomMaterialListController::class, 'destroy'])->whereNumber('bom')->name('destroy');
            });

            Route::get('/products', [ProductController::class, 'index'])->name('products.index');
            Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
            Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
            Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/products', [ProductController::class, 'store'])->name('products.store');
            Route::get('/products/versions', [ProductVersionController::class, 'index'])->name('products.versions');
            Route::get('/products/search', [ProductVersionController::class, 'searchProducts'])->name('products.search');
            Route::get('/products/{product}', [ProductController::class, 'show'])->whereNumber('product')->name('products.show');
            Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->whereNumber('product')->name('products.edit');
            Route::put('/products/{product}', [ProductController::class, 'update'])->whereNumber('product')->name('products.update');
            Route::delete('/products/{product}', [ProductController::class, 'destroy'])->whereNumber('product')->name('products.destroy');

            Route::prefix('/products/{product}/versions')->name('products.versions.')->group(function (): void {
                Route::get('/create', [ProductVersionController::class, 'create'])->whereNumber('product')->name('create');
                Route::post('/', [ProductVersionController::class, 'store'])->whereNumber('product')->name('store');
                Route::get('/{version}', [ProductVersionController::class, 'show'])->whereNumber('product')->whereNumber('version')->name('show');
                Route::get('/{version}/edit', [ProductVersionController::class, 'edit'])->whereNumber('product')->whereNumber('version')->name('edit');
                Route::put('/{version}', [ProductVersionController::class, 'update'])->whereNumber('product')->whereNumber('version')->name('update');
                Route::delete('/{version}', [ProductVersionController::class, 'destroy'])->whereNumber('product')->whereNumber('version')->name('destroy');
                Route::post('/{version}/approve', [ProductVersionController::class, 'approve'])->whereNumber('product')->whereNumber('version')->name('approve');
                Route::post('/{version}/obsolete', [ProductVersionController::class, 'obsolete'])->whereNumber('product')->whereNumber('version')->name('obsolete');
            });
        });
    });

});

Route::prefix('global-admin')->name('global-admin.')->middleware('auth:admin')->group(function (): void {
    Route::get('/', GlobalAdminHomeController::class)->name('home');
    Route::get('/docs', [DocumentationController::class, 'indexGlobal'])->name('docs.index');
    Route::get('/docs/{file}', [DocumentationController::class, 'showGlobal'])
        ->where('file', '[^/]+')
        ->name('docs.show');
    Route::get('/docs/dev/{file}', [DocumentationController::class, 'showDevGlobal'])
        ->where('file', '[^/]+')
        ->name('docs.dev.show');

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

    Route::get('/tutorials', [GlobalPageTutorialController::class, 'index'])->name('tutorials.index');
    Route::get('/tutorials/create', [GlobalPageTutorialController::class, 'create'])->name('tutorials.create');
    Route::post('/tutorials', [GlobalPageTutorialController::class, 'store'])->name('tutorials.store');
    Route::get('/tutorials/{tutorial}', [GlobalPageTutorialController::class, 'show'])->name('tutorials.show');
    Route::get('/tutorials/{tutorial}/edit', [GlobalPageTutorialController::class, 'edit'])->name('tutorials.edit');
    Route::put('/tutorials/{tutorial}', [GlobalPageTutorialController::class, 'update'])->name('tutorials.update');
    Route::delete('/tutorials/{tutorial}', [GlobalPageTutorialController::class, 'destroy'])->name('tutorials.destroy');
});
