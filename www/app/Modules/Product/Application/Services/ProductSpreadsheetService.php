<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\Services;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class ProductSpreadsheetService
{
    private const HEADERS = [
        'sku',
        'description',
        'product_type',
        'uom',
        'safety_stock',
        'lead_time_days',
        'lot_control',
        'serial_control',
        'is_active',
    ];

    private const ALLOWED_TYPES = ['FG', 'WIP', 'RAW', 'CONSUMABLE'];

    private const ALLOWED_BOOLEAN_VALUES = ['1', '0', 'true', 'false', 'yes', 'no', 'sim', 'não', 'nao', 's', 'n', 'on', 'off'];

    public function export(iterable $products, string $companyName): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products');
        $sheet->fromArray([self::HEADERS], null, 'A1');

        $rowNumber = 2;

        foreach ($products as $product) {
            if (! $product instanceof Product) {
                continue;
            }

            $sheet->fromArray([
                $product->sku,
                $product->description,
                $product->product_type,
                $product->uom,
                $product->safety_stock,
                $product->lead_time_days,
                $product->lot_control ? 1 : 0,
                $product->serial_control ? 1 : 0,
                $product->is_active ? 1 : 0,
            ], null, 'A'.$rowNumber);

            $rowNumber++;
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:I'.max(1, $rowNumber - 1));

        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        $tempDir = storage_path('app/tmp/product-spreadsheets');

        if (! File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0775, true);
        }

        $filePath = $tempDir.'/products-'.Str::slug($companyName ?: 'company').'-'.now()->format('YmdHis').'-'.Str::uuid().'.xlsx';

        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($filePath);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $filePath;
    }

    public function import(Company $company, UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if ($rows === []) {
            throw new DomainException(__('product.import_empty'));
        }

        $headerRow = array_shift($rows);
        $headers = $this->normalizeHeaders($headerRow);
        $this->assertHeaders($headers);

        $created = 0;
        $updated = 0;
        $processedRows = 0;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $normalizedRow = $this->normalizeRow($headers, $row, $rowNumber);
            $unit = $this->resolveUnit($company->id, $normalizedRow['uom'], $rowNumber);

            $product = Product::query()
                ->where('company_id', $company->id)
                ->where('sku', $normalizedRow['sku'])
                ->first();

            $attributes = [
                'company_id' => $company->id,
                'sku' => $normalizedRow['sku'],
                'description' => $normalizedRow['description'],
                'product_type' => $normalizedRow['product_type'],
                'uom' => mb_strtoupper((string) $unit->code),
                'unit_id' => (int) $unit->id,
                'safety_stock' => $normalizedRow['safety_stock'],
                'lead_time_days' => $normalizedRow['lead_time_days'],
                'lot_control' => $normalizedRow['lot_control'],
                'serial_control' => $normalizedRow['serial_control'],
                'is_active' => $normalizedRow['is_active'],
            ];

            if ($product) {
                $product->fill($attributes)->save();
                $updated++;
            } else {
                Product::query()->create($attributes);
                $created++;
            }

            $processedRows++;
        }

        if ($processedRows === 0) {
            throw new DomainException(__('product.import_empty'));
        }

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }

    /**
     * @param array<string, mixed> $headerRow
     * @return array<string, string>
     */
    private function normalizeHeaders(array $headerRow): array
    {
        $normalized = [];

        foreach ($headerRow as $column => $value) {
            $normalized[(string) $column] = strtolower(trim((string) $value));
        }

        return $normalized;
    }

    /**
     * @param array<string, string> $headers
     */
    private function assertHeaders(array $headers): void
    {
        $missing = [];

        foreach (self::HEADERS as $expectedHeader) {
            if (! in_array($expectedHeader, $headers, true)) {
                $missing[] = $expectedHeader;
            }
        }

        if ($missing !== []) {
            throw new DomainException(__('product.import_invalid_headers'), 422, ['missing' => $missing]);
        }
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $headers, array $row, int $rowNumber): array
    {
        $values = [];

        foreach ($headers as $column => $header) {
            $values[$header] = trim((string) ($row[$column] ?? ''));
        }

        $sku = $values['sku'] ?? '';
        $description = $values['description'] ?? '';
        $productType = strtoupper($values['product_type'] ?? '');
        $uom = $values['uom'] ?? '';

        if ($sku === '' || $description === '' || $productType === '' || $uom === '') {
            throw new DomainException(__('product.import_invalid_row', ['row' => $rowNumber]), 422);
        }

        if (! in_array($productType, self::ALLOWED_TYPES, true)) {
            throw new DomainException(__('product.import_invalid_type', ['row' => $rowNumber]), 422, [
                'product_type' => self::ALLOWED_TYPES,
            ]);
        }

        return [
            'sku' => $sku,
            'description' => $description,
            'product_type' => $productType,
            'uom' => $uom,
            'safety_stock' => $this->normalizeInteger($values['safety_stock'] ?? '0', $rowNumber, 'safety_stock'),
            'lead_time_days' => $this->normalizeInteger($values['lead_time_days'] ?? '0', $rowNumber, 'lead_time_days'),
            'lot_control' => $this->normalizeBoolean($values['lot_control'] ?? '0', $rowNumber, 'lot_control'),
            'serial_control' => $this->normalizeBoolean($values['serial_control'] ?? '0', $rowNumber, 'serial_control'),
            'is_active' => $this->normalizeBoolean($values['is_active'] ?? '1', $rowNumber, 'is_active'),
        ];
    }

    private function normalizeInteger(string $value, int $rowNumber, string $field): int
    {
        if ($value === '') {
            return 0;
        }

        if (! is_numeric($value)) {
            throw new DomainException(__('product.import_invalid_row', ['row' => $rowNumber]), 422, [
                $field => __('product.import_integer_required'),
            ]);
        }

        return (int) $value;
    }

    private function resolveUnit(int $companyId, string $uom, int $rowNumber): Unit
    {
        $code = mb_strtoupper(trim($uom));

        $unit = Unit::query()
            ->where('is_active', true)
            ->whereRaw('UPPER(code) = ?', [$code])
            ->orderByRaw('CASE WHEN company_id = ? THEN 0 ELSE 1 END', [$companyId])
            ->first();

        if (! $unit instanceof Unit) {
            throw new DomainException(__('product.import_invalid_row', ['row' => $rowNumber]), 422, [
                'uom' => [$code],
            ]);
        }

        return $unit;
    }

    private function normalizeBoolean(string $value, int $rowNumber, string $field): bool
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            return false;
        }

        if (! in_array($normalized, self::ALLOWED_BOOLEAN_VALUES, true)) {
            throw new DomainException(__('product.import_invalid_row', ['row' => $rowNumber]), 422, [
                $field => self::ALLOWED_BOOLEAN_VALUES,
            ]);
        }

        return in_array($normalized, ['1', 'true', 'yes', 'sim', 's', 'on'], true);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}