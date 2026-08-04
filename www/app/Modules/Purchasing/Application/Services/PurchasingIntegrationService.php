<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Application\Services;

use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseFiscalEntry;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseFiscalEntryPosting;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseReceipt;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseReceiptLine;
use DomainException;
use Illuminate\Support\Facades\DB;

final class PurchasingIntegrationService
{
    public function __construct(private readonly InventoryService $inventoryService)
    {
    }

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

    public function postFiscalEntryToFinancial(PurchaseFiscalEntry $entry, ?int $userId): PurchaseFiscalEntryPosting
    {
        $reference = $entry->financial_reference ?: sprintf('FIN-%s-%d', now()->format('YmdHis'), $entry->id);

        $posting = PurchaseFiscalEntryPosting::query()->updateOrCreate(
            [
                'company_id' => $entry->company_id,
                'purchase_fiscal_entry_id' => $entry->id,
            ],
            [
                'status' => 'POSTED',
                'financial_reference' => $reference,
                'posted_by' => $userId,
                'posted_at' => now(),
                'reversed_by' => null,
                'reversed_at' => null,
                'payload' => [
                    'entry_number' => $entry->entry_number,
                    'document_number' => $entry->document_number,
                    'amount_cents' => $entry->amount_cents,
                    'issue_date' => optional($entry->issue_date)->format('Y-m-d'),
                    'entry_date' => optional($entry->entry_date)->format('Y-m-d'),
                    'supplier_id' => $entry->supplier_id,
                    'purchase_order_id' => $entry->purchase_order_id,
                ],
            ]
        );

        $entry->financial_reference = $reference;
        $entry->financial_posted_at = now();
        $entry->save();

        return $posting;
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

                $this->inventoryService->postMovement([
                    'warehouse_id' => (int) $line->warehouse_id,
                    'product_id' => (int) $line->product_id,
                    'movement_type' => 'ISSUE',
                    'quantity' => (float) $line->quantity_received,
                    'lot_number' => $line->lot_number,
                    'reference_type' => 'purchase_receipt_reversal',
                    'reference_id' => $receipt->id,
                    'notes' => $line->notes,
                    'metadata' => [
                        'purchase_receipt_id' => $receipt->id,
                        'purchase_receipt_line_id' => $line->id,
                        'purchase_order_id' => $receipt->purchase_order_id,
                        'reversal_of_movement_id' => $line->stock_ledger_movement_id,
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

    public function reverseFiscalEntryPosting(PurchaseFiscalEntry $entry, string $category, string $reason, ?int $userId): PurchaseFiscalEntryPosting
    {
        $posting = PurchaseFiscalEntryPosting::query()
            ->where('company_id', $entry->company_id)
            ->where('purchase_fiscal_entry_id', $entry->id)
            ->first();

        if (! $posting instanceof PurchaseFiscalEntryPosting) {
            throw new DomainException('Fiscal entry has no financial posting to reverse.', 422);
        }

        if ($posting->status === 'REVERSED') {
            return $posting;
        }

        $posting->status = 'REVERSED';
        $posting->reversed_by = $userId;
        $posting->reversed_at = now();
        $payload = is_array($posting->payload) ? $posting->payload : [];
        $payload['reversal'] = [
            'category' => $category,
            'reason' => $reason,
            'reversed_by' => $userId,
            'reversed_at' => now()->toIso8601String(),
        ];
        $posting->payload = $payload;
        $posting->save();

        return $posting;
    }
}
