<?php

declare(strict_types=1);

namespace App\Modules\Production\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionOrderOutput extends TenantModel
{
    use HasFactory;

    protected $table = 'production_order_outputs';

    protected $fillable = [
        'company_id',
        'production_order_id',
        'quantity_completed',
        'quantity_scrapped',
        'lot_number',
        'produced_at',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'quantity_completed' => 'float',
        'quantity_scrapped' => 'float',
        'produced_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }
}
