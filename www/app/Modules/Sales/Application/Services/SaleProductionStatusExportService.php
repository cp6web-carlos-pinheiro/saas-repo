<?php

declare(strict_types=1);

namespace App\Modules\Sales\Application\Services;

use App\Modules\Sales\Infrastructure\Persistence\Models\Sale;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class SaleProductionStatusExportService
{
    public function excel(Sale $sale, array $analysis, string $companyName): string
    {
        $spreadsheet = new Spreadsheet();
        $summary = $spreadsheet->getActiveSheet();
        $summary->setTitle('Resumo');
        $summary->fromArray([
            ['Acompanhamento da produção', 'Venda #'.$sale->id],
            ['Empresa', $companyName],
            ['Cliente', $sale->customer?->name],
            ['Prontidão', $analysis['readiness']],
            ['Atendimento (%)', $analysis['progress_percent']],
            ['Data prometida', data_get($analysis, 'schedule.promised_date')],
            ['Conclusão projetada', $analysis['projected_completion']],
            ['Custo estimado', data_get($analysis, 'costs.estimated_total')],
            ['Custo realizado', data_get($analysis, 'costs.actual_total')],
            ['Desvio', data_get($analysis, 'costs.variance')],
            ['Margem estimada', data_get($analysis, 'costs.estimated_margin')],
        ], null, 'A1');
        $summary->getStyle('A1:A11')->getFont()->setBold(true);

        $materials = $spreadsheet->createSheet();
        $materials->setTitle('Materiais');
        $materials->fromArray([[
            'Item vendido', 'SKU', 'Descrição', 'Necessário', 'Reservado', 'Disponível',
            'Em produção', 'Em compra', 'Recebido', 'Falta líquida', 'Unidade', 'Custo unitário', 'Fonte do custo',
        ]], null, 'A1');
        $row = 2;

        foreach ($analysis['items'] as $item) {
            foreach ($item['materials'] as $material) {
                $materials->fromArray([[
                    $item['sku'], $material['sku'], $material['description'], $material['required_quantity'],
                    $material['reserved_quantity'], $material['available_quantity'], $material['in_production'],
                    $material['in_purchase'], $material['received_quantity'], $material['net_shortage'],
                    $material['unit'], $material['unit_cost'], trim(($material['cost_source'] ?? '').' '.($material['cost_reference'] ?? '')),
                ]], null, 'A'.$row++);
            }
        }

        $orders = $spreadsheet->createSheet();
        $orders->setTitle('Ordens de produção');
        $orders->fromArray([[
            'Item vendido', 'OP', 'SKU', 'Status', 'Planejado', 'Produzido', 'Progresso (%)',
            'Início', 'Término', 'Dias de atraso', 'Mão de obra estimada', 'Máquina estimada',
            'Material realizado', 'Mão de obra realizada', 'Máquina realizada', 'Refugo',
        ]], null, 'A1');
        $row = 2;

        foreach ($analysis['items'] as $item) {
            foreach ($item['production_orders'] as $order) {
                $orders->fromArray([[
                    $item['sku'], $order['order_number'], $order['sku'], $order['status'], $order['quantity_planned'],
                    $order['quantity_produced'], $order['progress_percent'], $order['scheduled_start'], $order['scheduled_end'],
                    $order['days_overdue'], data_get($order, 'costs.estimated_labor'), data_get($order, 'costs.estimated_machine'),
                    data_get($order, 'costs.actual_material'), data_get($order, 'costs.actual_labor'),
                    data_get($order, 'costs.actual_machine'), data_get($order, 'costs.actual_scrap'),
                ]], null, 'A'.$row++);
            }
        }

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $highestColumn = $sheet->getHighestColumn();
            $sheet->freezePane('A2');
            $sheet->getStyle('A1:'.$highestColumn.'1')->getFont()->setBold(true);
            foreach (range('A', $highestColumn) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        $path = $this->temporaryPath($sale, 'xlsx');
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }

    public function pdf(Sale $sale, array $analysis, string $companyName): string
    {
        $lines = [
            'ACOMPANHAMENTO DA PRODUCAO - VENDA #'.$sale->id,
            'Empresa: '.$companyName,
            'Cliente: '.($sale->customer?->name ?? '-'),
            'Prontidao: '.$analysis['readiness'].' | Atendimento: '.$analysis['progress_percent'].'%',
            'Data prometida: '.(data_get($analysis, 'schedule.promised_date') ?? '-').' | Conclusao projetada: '.($analysis['projected_completion'] ?? '-'),
            'Custo estimado: '.number_format((float) data_get($analysis, 'costs.estimated_total'), 2, ',', '.').' | Realizado: '.number_format((float) data_get($analysis, 'costs.actual_total'), 2, ',', '.'),
            'Margem estimada: '.number_format((float) data_get($analysis, 'costs.estimated_margin'), 2, ',', '.'),
            '',
        ];

        foreach ($analysis['items'] as $item) {
            $lines[] = $item['sku'].' - '.$item['description'].' | Qtd. '.$item['quantity'].' '.$item['unit'];

            foreach (array_merge($item['production_orders'], $item['forecasts']) as $order) {
                $lines[] = '  OP '.($order['order_number'] ?? 'prevista').' | '.$order['sku'].' | '.$order['status'].' | '.$order['progress_percent'].'% | '.($order['scheduled_end'] ?? '-');
            }

            foreach ($item['materials'] as $material) {
                $lines[] = '  MAT '.$material['sku'].' | nec. '.$material['required_quantity'].' | res. '.$material['reserved_quantity'].' | compra '.$material['in_purchase'].' | falta '.$material['net_shortage'];
            }

            $lines[] = '';
        }

        $path = $this->temporaryPath($sale, 'pdf');
        File::put($path, $this->simplePdf($lines));

        return $path;
    }

    private function temporaryPath(Sale $sale, string $extension): string
    {
        $directory = storage_path('app/tmp/sale-production-status');
        File::ensureDirectoryExists($directory);

        return $directory.'/sale-'.$sale->id.'-'.now()->format('YmdHis').'-'.Str::uuid().'.'.$extension;
    }

    /** @param list<string> $lines */
    private function simplePdf(array $lines): string
    {
        $pages = array_chunk($lines, 48);
        $pageCount = max(1, count($pages));
        $fontObject = 3 + ($pageCount * 2);
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Count '.$pageCount.' /Kids ['.implode(' ', array_map(static fn (int $index): string => (3 + ($index * 2)).' 0 R', range(0, $pageCount - 1))).'] >>',
        ];

        foreach ($pages ?: [[]] as $index => $pageLines) {
            $pageObject = 3 + ($index * 2);
            $contentObject = $pageObject + 1;
            $content = "BT\n/F1 9 Tf\n42 800 Td\n";

            foreach ($pageLines as $line) {
                $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $line) ?: $line;
                $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], mb_substr($ascii, 0, 115));
                $content .= '('.$escaped.") Tj\n0 -15 Td\n";
            }

            $content .= 'ET';
            $objects[$pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 '.$fontObject.' 0 R >> >> /Contents '.$contentObject.' 0 R >>';
            $objects[$contentObject] = '<< /Length '.strlen($content)." >>\nstream\n".$content."\nendstream";
        }

        $objects[$fontObject] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number." 0 obj\n".$object."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        for ($number = 1; $number <= count($objects); $number++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$number]);
        }

        return $pdf.'trailer'."\n<< /Size ".(count($objects) + 1).' /Root 1 0 R >>'."\nstartxref\n".$xref."\n%%EOF";
    }
}
