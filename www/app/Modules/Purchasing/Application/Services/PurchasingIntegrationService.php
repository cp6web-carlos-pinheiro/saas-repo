<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Application\Services;

use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseReceipt;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseReceiptLine;
use DomainException;
use Illuminate\Support\Facades\DB;

final class PurchasingIntegrationService
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function postReceiptToInventory(PurchaseReceipt $receipt, ?int $userId): void
    {
        $receipt->loadMissing(['lines', 'purchaseOrder']);

        DB::transaction(function () use ($receipt, $userId): void {
            /** @var PurchaseReceiptLine $line */
            foreach ($receipt->lines as $line) {
                if ((float) $line->quantity_received <= 0) {
                    continue;
                }

                $movement = $this->inventoryService->postMovement([
                    'warehouse_id' => (int) $line->warehouse_id,
                    'product_id' => (int) $line->product_id,
                    'movement_type' => 'RECEIPT',
                    'quantity' => (float) $line->quantity_received,
                    'lot_number' => $line->lot_number,
                    'reference_type' => 'purchase_receipt',
                    'reference_id' => $receipt->id,
                    'notes' => $line->notes,
                    'metadata' => [
                        'purchase_receipt_id' => $receipt->id,
                        'purchase_receipt_line_id' => $line->id,
                        'purchase_order_id' => $receipt->purchase_order_id,
                    ],
                    'movement_at' => $receipt->receipt_date?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ], $userId);

                $movementId = (int) ($movement['movement']['id'] ?? 0);

                $line->stock_ledger_movement_id = $movementId > 0 ? $movementId : null;
                $line->save();

                if ($line->purchase_order_line_id !== null) {
                    DB::table('purchase_order_lines')
                        ->where('id', $line->purchase_order_line_id)
                        ->update([
                            'quantity_received' => DB::raw('quantity_received + '.(float) $line->quantity_received),
                            'updated_at' => now(),
                        ]);
                }
            }
        });
    }

    public function reverseReceiptFromInventory(PurchaseReceipt $receipt, string $category, string $reason, ?int $userId): void
    {
        $receipt->loadMissing(['lines', 'purchaseOrder']);

        DB::transaction(function () use ($receipt, $category, $reason, $userId): void {
            /** @var PurchaseReceiptLine $line */
            foreach ($receipt->lines as $line) {
                if ((float) $line->quantity_received <= 0) {
                    continue;
                }

                if ($line->stock_ledger_movement_id === null) {
                    throw new DomainException('Receipt line has no stock ledger movement to reverse.', 422);
                }

                $this->inventoryService->reverseMovement([
                    'movement_id' => $line->stock_ledger_movement_id,
                    'reason' => $reason,
                    'metadata' => [
                        'purchase_receipt_id' => $receipt->id,
                        'purchase_receipt_line_id' => $line->id,
                        'purchase_order_id' => $receipt->purchase_order_id,
                        'reversal_category' => $category,
                        'reversal_reason' => $reason,
                    ],
                    'movement_at' => $receipt->receipt_date?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ], $userId);

                if ($line->purchase_order_line_id !== null) {
                    DB::table('purchase_order_lines')
                        ->where('id', $line->purchase_order_line_id)
                        ->update([
                            'quantity_received' => DB::raw('CASE WHEN quantity_received - '.(float) $line->quantity_received.' < 0 THEN 0 ELSE quantity_received - '.(float) $line->quantity_received.' END'),
                            'updated_at' => now(),
                        ]);
                }
            }
        });
    }
}
