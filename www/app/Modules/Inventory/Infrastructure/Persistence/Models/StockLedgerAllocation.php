<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Persistence\Models;

use App\Modules\Inventory\Infrastructure\Persistence\Models\StockLedgerMovement as StockLedgerMovementModel;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StockLedgerAllocation extends TenantModel
{
    use HasFactory;

    protected $table = 'stock_ledger_allocations';

    protected $fillable = [
        'company_id',
        'issue_movement_id',
        'receipt_movement_id',
        'quantity',
        'sequence_no',
    ];

    protected $casts = [
        'quantity' => 'float',
        'sequence_no' => 'integer',
    ];

    public function issueMovement(): BelongsTo
    {
        return $this->belongsTo(StockLedgerMovementModel::class, 'issue_movement_id');
    }

    public function receiptMovement(): BelongsTo
    {
        return $this->belongsTo(StockLedgerMovementModel::class, 'receipt_movement_id');
    }
}
