<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Persistence\Models;

use App\Modules\Bom\Infrastructure\Persistence\Models\BomHeader;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

final class Product extends TenantModel
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'company_id',
        'sku',
        'description',
        'product_type',
        'unit_id',
        'category_id',
        'brand_id',
        'safety_stock',
        'lead_time_days',
        'lot_control',
        'serial_control',
        'is_active',
        'lifecycle_status',
        'technical_attributes',
        'commercial_attributes',
        'alternate_uoms',
        'image_urls',
        'attachment_urls',
    ];

    protected $casts = [
        'safety_stock' => 'integer',
        'lead_time_days' => 'integer',
        'lot_control' => 'bool',
        'serial_control' => 'bool',
        'is_active' => 'bool',
        'technical_attributes' => 'array',
        'commercial_attributes' => 'array',
        'alternate_uoms' => 'array',
        'image_urls' => 'array',
        'attachment_urls' => 'array',
    ];

    protected static function booted(): void
    {
        parent::booted();

        self::saving(function (self $product): void {
            $unitId = (int) ($product->unit_id ?? 0);

            if ($unitId <= 0) {
                throw ValidationException::withMessages([
                    'unit_id' => ['The unit_id field is required.'],
                ]);
            }

            $unit = Unit::query()
                ->where('is_active', true)
                ->whereKey($unitId)
                ->first();

            if (! $unit instanceof Unit) {
                throw ValidationException::withMessages([
                    'unit_id' => ['The selected unit_id is invalid.'],
                ]);
            }

        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function getUomAttribute(): ?string
    {
        return $this->unit?->code;
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProductVersion::class);
    }

    public function bomHeaders(): HasMany
    {
        return $this->hasMany(BomHeader::class);
    }
}
