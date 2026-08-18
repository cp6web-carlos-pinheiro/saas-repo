<?php

declare(strict_types=1);

use App\Modules\Analysis\Presentation\Http\Controllers\ManufacturingAnalyticsController;
use App\Modules\Analysis\Presentation\Http\Controllers\ManufacturingReportController;
use App\Modules\Bom\Presentation\Http\Controllers\BomExplosionController;
use App\Modules\Eco\Presentation\Http\Controllers\EngineeringChangeOrderController;
use App\Modules\Genealogy\Presentation\Http\Controllers\GenealogyController;
use App\Modules\Identity\Presentation\Http\Controllers\AuthController;
use App\Modules\Identity\Presentation\Http\Controllers\SaaSOnboardingApiController;
use App\Modules\Identity\Presentation\Http\Middleware\CheckPermission;
use App\Modules\Inventory\Presentation\Http\Controllers\InventoryController;
use App\Modules\Inventory\Presentation\Http\Controllers\LotSerialTrackingController;
use App\Modules\MRP\Presentation\Http\Controllers\MRPHealthController;
use App\Modules\MRP\Presentation\Http\Controllers\MrpPlanningController;
use App\Modules\MRP\Presentation\Http\Controllers\MrpRecalculationController;
use App\Modules\MRP\Presentation\Http\Controllers\MrpSuggestionController;
use App\Modules\Product\Presentation\Http\Controllers\ProductController;
use App\Modules\Product\Presentation\Http\Controllers\ProductVersionController;
use App\Modules\Production\Presentation\Http\Controllers\ProductionMesController;
use App\Modules\Production\Presentation\Http\Controllers\ProductionOrderController;
use App\Modules\Purchasing\Presentation\Http\Controllers\PurchaseOrderController;
use App\Modules\Purchasing\Presentation\Http\Controllers\PurchaseRequisitionController;
use App\Modules\Purchasing\Presentation\Http\Controllers\SupplierController;
use App\Modules\Routing\Presentation\Http\Controllers\RoutingController;
use App\Modules\Routing\Presentation\Http\Controllers\RoutingStandardTimeController;
use App\Modules\Scheduling\Presentation\Http\Controllers\ProductionCalendarController;
use App\Modules\Scheduling\Presentation\Http\Controllers\ProductionResourceController;
use App\Modules\Scheduling\Presentation\Http\Controllers\ProductionScheduleController;
use App\Modules\Scheduling\Presentation\Http\Controllers\ProductionSchedulingController;
use App\Modules\Scheduling\Presentation\Http\Controllers\WorkCenterController;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Modules\Tenant\Presentation\Http\Middleware\ResolveTenant;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/register', [SaaSOnboardingApiController::class, 'register']);
    Route::post('/login', [SaaSOnboardingApiController::class, 'login']);
    Route::post('/forgot-password', [SaaSOnboardingApiController::class, 'forgotPassword']);
    Route::post('/reset-password', [SaaSOnboardingApiController::class, 'resetPassword']);
    Route::post('/verify-email', [SaaSOnboardingApiController::class, 'verifyEmail']);
    Route::post('/social-login', [SaaSOnboardingApiController::class, 'socialLogin']);

    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/jwt/login', [AuthController::class, 'loginJwt']);

    Route::middleware(['auth:jwt'])->group(function (): void {
        Route::get('/auth/jwt/me', [AuthController::class, 'meJwt']);
        Route::post('/auth/jwt/refresh', [AuthController::class, 'refreshJwt']);
        Route::post('/auth/jwt/logout', [AuthController::class, 'logoutJwt']);
    });

    Route::middleware(['auth:sanctum,jwt'])->group(function (): void {
        Route::post('/logout', [SaaSOnboardingApiController::class, 'logout']);
        Route::post('/resend-verification', [SaaSOnboardingApiController::class, 'resendVerification']);
        Route::post('/onboarding', [SaaSOnboardingApiController::class, 'onboarding']);
        Route::get('/trial/status', [SaaSOnboardingApiController::class, 'trialStatus']);
        Route::get('/tenant-management', [SaaSOnboardingApiController::class, 'tenantManagement']);
        Route::post('/tenant/users', [SaaSOnboardingApiController::class, 'createTenantUser']);

        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/health/mrp', MRPHealthController::class);

        Route::middleware([ResolveTenant::class])->group(function (): void {
            Route::get('/tenant/plants', static fn () => ApiResponse::success(Plant::query()->paginate(15), 'Tenant plants'));

            Route::get('/tenant/permissions-check', static function () {
                return ApiResponse::success(['ok' => true], 'Permission granted');
            })->middleware(CheckPermission::class.':plants.read');

            Route::get('/products', [ProductController::class, 'index'])
                ->middleware(CheckPermission::class.':products.read');
            Route::post('/products', [ProductController::class, 'store'])
                ->middleware(CheckPermission::class.':products.create');
            Route::post('/products/bulk', [ProductController::class, 'bulkStore'])
                ->middleware(CheckPermission::class.':products.create');
            Route::put('/products/bulk', [ProductController::class, 'bulkUpdate'])
                ->middleware(CheckPermission::class.':products.update');
            Route::get('/products/{id}', [ProductController::class, 'show'])
                ->middleware(CheckPermission::class.':products.read');
            Route::put('/products/{id}', [ProductController::class, 'update'])
                ->middleware(CheckPermission::class.':products.update');
            Route::delete('/products/{id}', [ProductController::class, 'destroy'])
                ->middleware(CheckPermission::class.':products.delete');

            Route::get('/products/{productId}/versions', [ProductVersionController::class, 'history'])
                ->middleware(CheckPermission::class.':products.versions.read');
            Route::post('/products/{productId}/versions', [ProductVersionController::class, 'store'])
                ->middleware(CheckPermission::class.':products.versions.create');
            Route::get('/products/{productId}/versions/effective', [ProductVersionController::class, 'effective'])
                ->middleware(CheckPermission::class.':products.versions.read');
            Route::get('/products/{productId}/versions/{versionId}', [ProductVersionController::class, 'show'])
                ->middleware(CheckPermission::class.':products.versions.read');
            Route::put('/products/{productId}/versions/{versionId}', [ProductVersionController::class, 'update'])
                ->middleware(CheckPermission::class.':products.versions.update');
            Route::post('/products/{productId}/versions/{versionId}/approve', [ProductVersionController::class, 'approve'])
                ->middleware(CheckPermission::class.':products.versions.approve');
            Route::post('/products/{productId}/versions/{versionId}/obsolete', [ProductVersionController::class, 'obsolete'])
                ->middleware(CheckPermission::class.':products.versions.obsolete');

            Route::get('/work-centers', [WorkCenterController::class, 'index'])
                ->middleware(CheckPermission::class.':work-centers.read');
            Route::post('/work-centers', [WorkCenterController::class, 'store'])
                ->middleware(CheckPermission::class.':work-centers.create');
            Route::get('/work-centers/{id}', [WorkCenterController::class, 'show'])
                ->middleware(CheckPermission::class.':work-centers.read');
            Route::put('/work-centers/{id}', [WorkCenterController::class, 'update'])
                ->middleware(CheckPermission::class.':work-centers.update');
            Route::delete('/work-centers/{id}', [WorkCenterController::class, 'destroy'])
                ->middleware(CheckPermission::class.':work-centers.delete');
            Route::post('/work-centers/{id}/shifts', [WorkCenterController::class, 'addShift'])
                ->middleware(CheckPermission::class.':work-centers.shifts.create');

            Route::get('/production-resources', [ProductionResourceController::class, 'index'])
                ->middleware(CheckPermission::class.':production-resources.read');
            Route::post('/production-resources', [ProductionResourceController::class, 'store'])
                ->middleware(CheckPermission::class.':production-resources.create');
            Route::get('/production-resources/{id}', [ProductionResourceController::class, 'show'])
                ->middleware(CheckPermission::class.':production-resources.read');
            Route::put('/production-resources/{id}', [ProductionResourceController::class, 'update'])
                ->middleware(CheckPermission::class.':production-resources.update');
            Route::delete('/production-resources/{id}', [ProductionResourceController::class, 'destroy'])
                ->middleware(CheckPermission::class.':production-resources.delete');
            Route::get('/work-centers/{workCenterId}/hour-rates', [ProductionResourceController::class, 'rates'])
                ->middleware(CheckPermission::class.':work-center-hour-rates.read');
            Route::post('/work-centers/{workCenterId}/hour-rates', [ProductionResourceController::class, 'storeRate'])
                ->middleware(CheckPermission::class.':work-center-hour-rates.create');
            Route::get('/work-centers/{workCenterId}/hour-rates/effective', [ProductionResourceController::class, 'effectiveRate'])
                ->middleware(CheckPermission::class.':work-center-hour-rates.read');

            Route::get('/work-centers/{workCenterId}/calendar', [ProductionCalendarController::class, 'index'])
                ->middleware(CheckPermission::class.':production-calendar.read');
            Route::put('/work-centers/{workCenterId}/calendar/day', [ProductionCalendarController::class, 'upsert'])
                ->middleware(CheckPermission::class.':production-calendar.update');
            Route::post('/work-centers/{workCenterId}/calendar/generate', [ProductionCalendarController::class, 'bulkGenerate'])
                ->middleware(CheckPermission::class.':production-calendar.generate');

            Route::post('/scheduling/production-orders', [ProductionSchedulingController::class, 'run'])
                ->middleware(CheckPermission::class.':production-scheduling.run');
            Route::get('/production-schedules', [ProductionScheduleController::class, 'index'])
                ->middleware(CheckPermission::class.':production-schedules.read');
            Route::post('/production-schedules', [ProductionScheduleController::class, 'store'])
                ->middleware(CheckPermission::class.':production-schedules.create');
            Route::get('/production-schedules/{id}', [ProductionScheduleController::class, 'show'])
                ->middleware(CheckPermission::class.':production-schedules.read');
            Route::post('/production-schedules/{id}/publish', [ProductionScheduleController::class, 'publish'])
                ->middleware(CheckPermission::class.':production-schedules.publish');
            Route::post('/production-schedules/{id}/cancel', [ProductionScheduleController::class, 'cancel'])
                ->middleware(CheckPermission::class.':production-schedules.cancel');
            Route::get('/production-schedules/{id}/compare/{otherId}', [ProductionScheduleController::class, 'compare'])
                ->middleware(CheckPermission::class.':production-schedules.compare');

            Route::post('/bom/explode', BomExplosionController::class)
                ->middleware(CheckPermission::class.':bom.explode');

            Route::get('/analytics/manufacturing/overview', [ManufacturingAnalyticsController::class, 'overview'])
                ->middleware(CheckPermission::class.':analytics.manufacturing.read');
            Route::get('/analytics/manufacturing/efficiency', [ManufacturingAnalyticsController::class, 'efficiency'])
                ->middleware(CheckPermission::class.':analytics.manufacturing.read');
            Route::get('/analytics/manufacturing/oee', [ManufacturingAnalyticsController::class, 'oee'])
                ->middleware(CheckPermission::class.':analytics.manufacturing.read');
            Route::get('/analytics/manufacturing/standard-times', [ManufacturingAnalyticsController::class, 'standardTimes'])
                ->middleware(CheckPermission::class.':analytics.manufacturing.read');
            Route::post('/analytics/manufacturing/standard-times/recommend', [ManufacturingAnalyticsController::class, 'recommend'])
                ->middleware(CheckPermission::class.':analytics.standard-times.recommend');
            Route::post('/analytics/manufacturing/standard-times/recommendations/{id}/decide', [ManufacturingAnalyticsController::class, 'decide'])
                ->middleware(CheckPermission::class.':analytics.standard-times.decide');
            Route::get('/manufacturing-reports/{type}', [ManufacturingReportController::class, 'show'])
                ->middleware(CheckPermission::class.':manufacturing-reports.read');
            Route::get('/manufacturing-reports/{type}/export', [ManufacturingReportController::class, 'export'])
                ->middleware(CheckPermission::class.':manufacturing-reports.export');

            Route::post('/mrp/plan', [MrpPlanningController::class, 'run'])
                ->middleware(CheckPermission::class.':mrp.plan');
            Route::post('/mrp/recalculate', [MrpRecalculationController::class, 'run'])
                ->middleware(CheckPermission::class.':mrp.plan');
            Route::get('/mrp/runs', [MrpSuggestionController::class, 'runs'])
                ->middleware(CheckPermission::class.':mrp-runs.read');
            Route::get('/mrp/suggestions', [MrpSuggestionController::class, 'index'])
                ->middleware(CheckPermission::class.':mrp-suggestions.read');
            Route::get('/mrp/suggestions/{id}', [MrpSuggestionController::class, 'show'])
                ->middleware(CheckPermission::class.':mrp-suggestions.read');
            Route::post('/mrp/suggestions/{id}/approve', [MrpSuggestionController::class, 'approve'])
                ->middleware(CheckPermission::class.':mrp-suggestions.approve');
            Route::post('/mrp/suggestions/{id}/reject', [MrpSuggestionController::class, 'reject'])
                ->middleware(CheckPermission::class.':mrp-suggestions.reject');
            Route::post('/mrp/suggestions/{id}/convert', [MrpSuggestionController::class, 'convert'])
                ->middleware(CheckPermission::class.':mrp-suggestions.convert');

            Route::get('/genealogy/trace', [GenealogyController::class, 'trace'])
                ->middleware(CheckPermission::class.':genealogy.trace');
            Route::post('/genealogy/production-orders/{productionOrderId}/lot', [GenealogyController::class, 'linkLotProductionOutput'])
                ->middleware(CheckPermission::class.':genealogy.relations.create');
            Route::post('/genealogy/material-consumptions/{consumptionId}', [GenealogyController::class, 'linkMaterialConsumption'])
                ->middleware(CheckPermission::class.':genealogy.relations.create');
            Route::post('/genealogy/lot-serial', [GenealogyController::class, 'linkLotSerial'])
                ->middleware(CheckPermission::class.':genealogy.relations.create');

            Route::get('/ecos', [EngineeringChangeOrderController::class, 'index'])
                ->middleware(CheckPermission::class.':eco.read');
            Route::post('/ecos', [EngineeringChangeOrderController::class, 'store'])
                ->middleware(CheckPermission::class.':eco.create');
            Route::get('/ecos/{id}', [EngineeringChangeOrderController::class, 'show'])
                ->middleware(CheckPermission::class.':eco.read');
            Route::put('/ecos/{id}', [EngineeringChangeOrderController::class, 'update'])
                ->middleware(CheckPermission::class.':eco.update');
            Route::post('/ecos/{id}/submit', [EngineeringChangeOrderController::class, 'submit'])
                ->middleware(CheckPermission::class.':eco.submit');
            Route::post('/ecos/{id}/approve', [EngineeringChangeOrderController::class, 'approve'])
                ->middleware(CheckPermission::class.':eco.approve');
            Route::post('/ecos/{id}/reject', [EngineeringChangeOrderController::class, 'reject'])
                ->middleware(CheckPermission::class.':eco.reject');
            Route::post('/ecos/{id}/implement', [EngineeringChangeOrderController::class, 'implement'])
                ->middleware(CheckPermission::class.':eco.implement');
            Route::get('/ecos/{id}/impact', [EngineeringChangeOrderController::class, 'impact'])
                ->middleware(CheckPermission::class.':eco.impact.read');

            Route::get('/routing-versions', [RoutingController::class, 'indexVersions'])
                ->middleware(CheckPermission::class.':routing-versions.read');
            Route::post('/routing-versions', [RoutingController::class, 'storeVersion'])
                ->middleware(CheckPermission::class.':routing-versions.create');
            Route::get('/routing-versions/{id}', [RoutingController::class, 'showVersion'])
                ->middleware(CheckPermission::class.':routing-versions.read');
            Route::post('/routing-versions/{id}/approve', [RoutingController::class, 'approve'])
                ->middleware(CheckPermission::class.':routing-versions.approve');
            Route::post('/routing-versions/{id}/obsolete', [RoutingController::class, 'obsolete'])
                ->middleware(CheckPermission::class.':routing-versions.obsolete');

            Route::post('/routing-versions/{routingVersionId}/operations', [RoutingController::class, 'storeOperation'])
                ->middleware(CheckPermission::class.':routing-operations.create');
            Route::put('/routing-versions/{routingVersionId}/operations/{operationId}', [RoutingController::class, 'updateOperation'])
                ->middleware(CheckPermission::class.':routing-operations.update');
            Route::delete('/routing-versions/{routingVersionId}/operations/{operationId}', [RoutingController::class, 'destroyOperation'])
                ->middleware(CheckPermission::class.':routing-operations.delete');

            Route::get('/routing-operations/{routingOperationId}/standard-times', [RoutingStandardTimeController::class, 'index'])
                ->middleware(CheckPermission::class.':routing-standard-times.read');
            Route::post('/routing-operations/{routingOperationId}/standard-times', [RoutingStandardTimeController::class, 'store'])
                ->middleware(CheckPermission::class.':routing-standard-times.create');
            Route::put('/routing-standard-times/{id}', [RoutingStandardTimeController::class, 'update'])
                ->middleware(CheckPermission::class.':routing-standard-times.update');
            Route::post('/routing-standard-times/{id}/approve', [RoutingStandardTimeController::class, 'approve'])
                ->middleware(CheckPermission::class.':routing-standard-times.approve');
            Route::post('/routing-standard-times/{id}/obsolete', [RoutingStandardTimeController::class, 'obsolete'])
                ->middleware(CheckPermission::class.':routing-standard-times.obsolete');
            Route::get('/routing-operations/{routingOperationId}/standard-times/effective', [RoutingStandardTimeController::class, 'effective'])
                ->middleware(CheckPermission::class.':routing-standard-times.read');
            Route::post('/routing-operations/{routingOperationId}/standard-times/calculate', [RoutingStandardTimeController::class, 'calculate'])
                ->middleware(CheckPermission::class.':routing-standard-times.read');

            Route::get('/inventory/balances', [InventoryController::class, 'index'])
                ->middleware(CheckPermission::class.':inventory.read');
            Route::put('/inventory/balances', [InventoryController::class, 'upsert'])
                ->middleware(CheckPermission::class.':inventory.update');
            Route::post('/inventory/balances/adjust', [InventoryController::class, 'adjust'])
                ->middleware(CheckPermission::class.':inventory.update');
            Route::get('/inventory/ledger', [InventoryController::class, 'ledger'])
                ->middleware(CheckPermission::class.':inventory.read');
            Route::post('/inventory/ledger/movements', [InventoryController::class, 'storeMovement'])
                ->middleware(CheckPermission::class.':inventory.update');

            Route::get('/inventory/reservations', [InventoryController::class, 'reservations'])
                ->middleware(CheckPermission::class.':inventory.read');
            Route::post('/inventory/reservations', [InventoryController::class, 'storeReservation'])
                ->middleware(CheckPermission::class.':inventory.update');
            Route::post('/inventory/reservations/release-expired', [InventoryController::class, 'releaseExpiredReservations'])
                ->middleware(CheckPermission::class.':inventory.update');
            Route::post('/inventory/reservations/{reservationId}/release', [InventoryController::class, 'releaseReservation'])
                ->middleware(CheckPermission::class.':inventory.update');

            Route::get('/inventory/lots', [LotSerialTrackingController::class, 'lots'])
                ->middleware(CheckPermission::class.':inventory.lots.read');
            Route::post('/inventory/lots', [LotSerialTrackingController::class, 'storeLot'])
                ->middleware(CheckPermission::class.':inventory.lots.create');
            Route::get('/inventory/lots/{lotNumber}/trace', [LotSerialTrackingController::class, 'traceLot'])
                ->middleware(CheckPermission::class.':inventory.lots.trace');

            Route::get('/inventory/serials', [LotSerialTrackingController::class, 'serials'])
                ->middleware(CheckPermission::class.':inventory.serials.read');
            Route::post('/inventory/serials', [LotSerialTrackingController::class, 'storeSerial'])
                ->middleware(CheckPermission::class.':inventory.serials.create');
            Route::get('/inventory/serials/{serialNumber}/trace', [LotSerialTrackingController::class, 'traceSerial'])
                ->middleware(CheckPermission::class.':inventory.serials.trace');

            Route::get('/production-orders', [ProductionOrderController::class, 'index'])
                ->middleware(CheckPermission::class.':production-orders.read');
            Route::get('/production-orders/{id}', [ProductionOrderController::class, 'show'])
                ->middleware(CheckPermission::class.':production-orders.read');
            Route::post('/production-orders/manual', [ProductionOrderController::class, 'storeManual'])
                ->middleware(CheckPermission::class.':production-orders.create');
            Route::post('/production-orders/mrp', [ProductionOrderController::class, 'storeMrp'])
                ->middleware(CheckPermission::class.':production-orders.create');
            Route::post('/production-orders/{id}/release', [ProductionOrderController::class, 'release'])
                ->middleware(CheckPermission::class.':production-orders.release');
            Route::post('/production-orders/{id}/partial', [ProductionOrderController::class, 'partial'])
                ->middleware(CheckPermission::class.':production-orders.partial');
            Route::post('/production-orders/{id}/complete', [ProductionOrderController::class, 'complete'])
                ->middleware(CheckPermission::class.':production-orders.complete');
            Route::get('/production-orders/{id}/snapshot', [ProductionOrderController::class, 'snapshot'])
                ->middleware(CheckPermission::class.':production-orders.read');
            Route::get('/production-orders/{id}/operations', [ProductionOrderController::class, 'operations'])
                ->middleware(CheckPermission::class.':production-order-operations.read');
            Route::post('/production-orders/{id}/operations/materialize', [ProductionOrderController::class, 'materializeOperations'])
                ->middleware(CheckPermission::class.':production-order-operations.plan');
            Route::get('/production-orders/{id}/consumptions', [ProductionOrderController::class, 'consumptions'])
                ->middleware(CheckPermission::class.':production-orders.consumption.read');
            Route::post('/production-orders/{id}/consumptions', [ProductionOrderController::class, 'recordConsumption'])
                ->middleware(CheckPermission::class.':production-orders.consumption.create');
            Route::get('/production-orders/{id}/consumptions/summary', [ProductionOrderController::class, 'consumptionSummary'])
                ->middleware(CheckPermission::class.':production-orders.consumption.read');
            Route::post('/material-consumptions/{consumptionId}/reverse', [ProductionOrderController::class, 'reverseConsumption'])
                ->middleware(CheckPermission::class.':production-orders.consumption.reverse');

            Route::get('/production-operations/{id}/mes', [ProductionMesController::class, 'show'])
                ->middleware(CheckPermission::class.':mes-operations.read');
            Route::post('/production-operations/{id}/start', [ProductionMesController::class, 'start'])
                ->middleware(CheckPermission::class.':mes-operations.execute');
            Route::post('/production-operations/{id}/pause', [ProductionMesController::class, 'pause'])
                ->middleware(CheckPermission::class.':mes-operations.execute');
            Route::post('/production-operations/{id}/resume', [ProductionMesController::class, 'resume'])
                ->middleware(CheckPermission::class.':mes-operations.execute');
            Route::post('/production-operations/{id}/stop', [ProductionMesController::class, 'stop'])
                ->middleware(CheckPermission::class.':mes-operations.execute');
            Route::post('/production-operations/{id}/complete', [ProductionMesController::class, 'complete'])
                ->middleware(CheckPermission::class.':mes-operations.execute');
            Route::post('/production-operations/{id}/cancel', [ProductionMesController::class, 'cancel'])
                ->middleware(CheckPermission::class.':mes-operations.correct');
            Route::post('/production-operations/{id}/outputs', [ProductionMesController::class, 'output'])
                ->middleware(CheckPermission::class.':mes-operations.report');
            Route::post('/production-operations/{id}/quality', [ProductionMesController::class, 'quality'])
                ->middleware(CheckPermission::class.':mes-quality.report');
            Route::post('/production-operations/{id}/rework', [ProductionMesController::class, 'rework'])
                ->middleware(CheckPermission::class.':mes-quality.rework');
            Route::post('/production-rework/{id}/complete', [ProductionMesController::class, 'completeRework'])
                ->middleware(CheckPermission::class.':mes-quality.rework');

            Route::get('/purchasing/suppliers', [SupplierController::class, 'index'])
                ->middleware(CheckPermission::class.':purchasing.suppliers.read');
            Route::post('/purchasing/suppliers', [SupplierController::class, 'store'])
                ->middleware(CheckPermission::class.':purchasing.suppliers.create');
            Route::put('/purchasing/suppliers/{id}', [SupplierController::class, 'update'])
                ->middleware(CheckPermission::class.':purchasing.suppliers.update');
            Route::get('/purchasing/suppliers/{supplierId}/rules', [SupplierController::class, 'rules'])
                ->middleware(CheckPermission::class.':purchasing.suppliers.read');
            Route::put('/purchasing/suppliers/{supplierId}/products/{productId}/rule', [SupplierController::class, 'upsertRule'])
                ->middleware(CheckPermission::class.':purchasing.supplier-rules.manage');

            Route::get('/purchasing/requisitions', [PurchaseRequisitionController::class, 'index'])
                ->middleware(CheckPermission::class.':purchasing.requisitions.read');
            Route::post('/purchasing/requisitions', [PurchaseRequisitionController::class, 'store'])
                ->middleware(CheckPermission::class.':purchasing.requisitions.create');
            Route::post('/purchasing/requisitions/from-mrp', [PurchaseRequisitionController::class, 'storeFromMrp'])
                ->middleware(CheckPermission::class.':purchasing.requisitions.from-mrp');
            Route::get('/purchasing/requisitions/{id}', [PurchaseRequisitionController::class, 'show'])
                ->middleware(CheckPermission::class.':purchasing.requisitions.read');
            Route::post('/purchasing/requisitions/{id}/convert-to-po', [PurchaseRequisitionController::class, 'convertToPurchaseOrders'])
                ->middleware(CheckPermission::class.':purchasing.requisitions.convert');

            Route::get('/purchasing/orders', [PurchaseOrderController::class, 'index'])
                ->middleware(CheckPermission::class.':purchasing.orders.read');
            Route::get('/purchasing/orders/{id}', [PurchaseOrderController::class, 'show'])
                ->middleware(CheckPermission::class.':purchasing.orders.read');
            Route::post('/purchasing/orders/{id}/approve', [PurchaseOrderController::class, 'approve'])
                ->middleware(CheckPermission::class.':purchasing.orders.approve');
        });
    });
});
