<?php

declare(strict_types=1);

namespace App\Modules\Analysis\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;

final class ManufacturingAnalyticsRecommendation extends TenantModel
{
    protected $table = 'manufacturing_analytics_recommendations';

    protected $fillable = ['company_id', 'production_order_operation_id', 'routing_operation_id', 'standard_time_id', 'standard_time_version', 'status', 'current_time_minutes', 'suggested_time_minutes', 'sample_size', 'statistics', 'filters', 'decision_reason', 'decided_by', 'decided_at'];

    protected $casts = ['current_time_minutes' => 'float', 'suggested_time_minutes' => 'float', 'statistics' => 'array', 'filters' => 'array', 'decided_at' => 'datetime'];
}
