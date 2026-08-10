@extends('layouts.client-area')

@section('title', __('sale.production_status.title'))
@section('client-page-title', __('sale.production_status.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    @php
        $formatQuantity = static fn (float|int $quantity): string => rtrim(rtrim(number_format((float) $quantity, 6, ',', '.'), '0'), ',');
        $formatMoney = static fn (float|int $amount): string => 'R$ '.number_format((float) $amount, 2, ',', '.');
        $statusPresentation = static fn (string $status): array => match ($status) {
            'completed' => [__('sale.production_status.completed'), 'bg-[#e6f4ea] text-[#137333]'],
            'in_progress' => [__('sale.production_status.in_progress'), 'bg-[#e8f0fe] text-[#174ea6]'],
            'planned' => [__('sale.production_status.planned'), 'bg-[#fef7e0] text-[#8a5a00]'],
            'available' => [__('sale.production_status.available'), 'bg-[#e6f4ea] text-[#137333]'],
            default => [__('sale.production_status.forecast'), 'bg-[#f3e8fd] text-[#7627bb]'],
        };
        $readinessPresentation = match ($analysis['readiness']) {
            'ready' => [__('sale.production_status.readiness_ready'), 'bg-[#e6f4ea] text-[#137333]', 'bg-[#34a853]'],
            'in_progress' => [__('sale.production_status.readiness_in_progress'), 'bg-[#e8f0fe] text-[#174ea6]', 'bg-[#1a73e8]'],
            'planned' => [__('sale.production_status.readiness_planned'), 'bg-[#fef7e0] text-[#8a5a00]', 'bg-[#f9ab00]'],
            'blocked_materials' => [__('sale.production_status.readiness_blocked_materials'), 'bg-[#fce8e6] text-[#b3261e]', 'bg-[#d93025]'],
            'blocked_structure' => [__('sale.production_status.readiness_blocked_structure'), 'bg-[#fce8e6] text-[#b3261e]', 'bg-[#d93025]'],
            'at_risk' => [__('sale.production_status.readiness_at_risk'), 'bg-[#fce8e6] text-[#b3261e]', 'bg-[#d93025]'],
            default => [__('sale.production_status.readiness_attention'), 'bg-[#fef7e0] text-[#8a5a00]', 'bg-[#f9ab00]'],
        };
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-[#5f6368]">{{ __('sale.reference_label', ['id' => $sale->id]) }}</p>
            <h1 class="mt-1 font-display text-3xl font-bold">{{ __('sale.production_status.title') }}</h1>
            <p class="mt-2 text-sm text-[#5f6368]">{{ __('sale.production_status.subtitle', ['customer' => $sale->customer?->name ?? __('sale.customer_removed')]) }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('sales.production-status', $sale)" variant="surface-muted" class="rounded-full">{{ __('sale.production_status.refresh') }}</x-ui.button>
            <x-ui.button :href="route('sales.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr_1fr] lg:items-center">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full px-3 py-1.5 text-sm font-semibold {{ $readinessPresentation[1] }}">{{ $readinessPresentation[0] }}</span>
                    <span class="text-xs text-[#5f6368]">{{ __('sale.production_status.last_updated', ['date' => \Illuminate\Support\Carbon::parse($analysis['last_updated_at'])->format('d/m/Y H:i')]) }}</span>
                </div>
                <div class="mt-4 flex items-end justify-between gap-4">
                    <div>
                        <div class="text-sm text-[#5f6368]">{{ __('sale.production_status.fulfillment_progress') }}</div>
                        <div class="mt-1 text-3xl font-bold">{{ number_format($analysis['progress_percent'], 1, ',', '.') }}%</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-[#5f6368]">{{ __('sale.production_status.projected_completion') }}</div>
                        <div class="mt-1 font-semibold">{{ $analysis['projected_completion'] ? \Illuminate\Support\Carbon::parse($analysis['projected_completion'])->format('d/m/Y') : __('sale.production_status.not_scheduled') }}</div>
                    </div>
                </div>
                <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-[#e8eaed]"><div class="h-full rounded-full {{ $readinessPresentation[2] }}" style="width: {{ $analysis['progress_percent'] }}%"></div></div>
            </div>
            <div class="border-[#dadce0] lg:border-l lg:pl-6">
                <div class="text-sm text-[#5f6368]">{{ __('sale.production_status.cost_variance') }}</div>
                <div class="mt-1 text-2xl font-bold {{ $analysis['costs']['variance'] > 0 ? 'text-[#b3261e]' : 'text-[#137333]' }}">{{ $formatMoney($analysis['costs']['variance']) }}</div>
                <div class="mt-1 text-xs text-[#5f6368]">{{ $analysis['costs']['variance_percent'] !== null ? number_format($analysis['costs']['variance_percent'], 1, ',', '.').'%' : '—' }}</div>
            </div>
            <div class="border-[#dadce0] lg:border-l lg:pl-6">
                <div class="text-sm text-[#5f6368]">{{ __('sale.production_status.exceptions') }}</div>
                <div class="mt-1 text-2xl font-bold {{ count($analysis['alerts']) > 0 ? 'text-[#b3261e]' : 'text-[#137333]' }}">{{ count($analysis['alerts']) }}</div>
                <div class="mt-1 text-xs text-[#5f6368]">{{ __('sale.production_status.overdue_orders', ['count' => $analysis['counts']['overdue']]) }}</div>
            </div>
        </div>
    </x-ui.panel>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            ['completed', $analysis['counts']['completed'], 'text-[#137333]'],
            ['in_progress', $analysis['counts']['in_progress'], 'text-[#174ea6]'],
            ['planned', $analysis['counts']['planned'], 'text-[#8a5a00]'],
            ['forecast', $analysis['counts']['forecast'], 'text-[#7627bb]'],
        ] as [$status, $count, $tone])
            <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
                <div class="text-sm text-[#5f6368]">{{ __('sale.production_status.'.$status) }}</div>
                <div class="mt-1 text-3xl font-bold {{ $tone }}">{{ $count }}</div>
            </x-ui.panel>
        @endforeach
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
            <div class="text-sm text-[#5f6368]">{{ __('sale.production_status.estimated_cost') }}</div>
            <div class="mt-1 text-xl font-bold">{{ $formatMoney($analysis['costs']['estimated_total']) }}{{ $analysis['costs']['estimated_incomplete'] ? '*' : '' }}</div>
        </x-ui.panel>
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
            <div class="text-sm text-[#5f6368]">{{ __('sale.production_status.actual_cost') }}</div>
            <div class="mt-1 text-xl font-bold">{{ $formatMoney($analysis['costs']['actual_total']) }}{{ $analysis['costs']['actual_incomplete'] ? '*' : '' }}</div>
        </x-ui.panel>
    </div>

    @if ($analysis['costs']['estimated_incomplete'] || $analysis['costs']['actual_incomplete'])
        <x-ui.alert class="mt-4" variant="warning">{{ __('sale.production_status.partial_cost_hint') }}</x-ui.alert>
    @endif

    @if ($analysis['alerts'] !== [])
        <x-ui.panel class="mt-4 border-[#dadce0] shadow-none" padding="p-5">
            <h2 class="font-semibold">{{ __('sale.production_status.alert_center') }}</h2>
            <div class="mt-3 grid gap-2 md:grid-cols-2">
                @foreach ($analysis['alerts'] as $alert)
                    <button type="button" class="rounded-xl border px-3 py-2 text-left text-sm transition hover:bg-white {{ $alert['severity'] === 'error' ? 'border-[#f6aea9] bg-[#fce8e6] text-[#b3261e]' : ($alert['severity'] === 'warning' ? 'border-[#f9d98c] bg-[#fef7e0] text-[#8a5a00]' : 'border-[#aecbfa] bg-[#e8f0fe] text-[#174ea6]') }}" data-alert-line="{{ $alert['line_id'] }}">
                        {{ __('sale.production_status.alerts.'.$alert['key'], ['product' => $alert['product'] ?? '', 'count' => $alert['count'] ?? 0]) }}
                    </button>
                @endforeach
            </div>
        </x-ui.panel>
    @endif

    <div class="mt-6 flex flex-wrap items-center gap-2" role="group" aria-label="{{ __('sale.production_status.filters') }}">
        @foreach ([
            'all' => __('sale.production_status.filter_all'),
            'attention' => __('sale.production_status.filter_attention'),
            'shortage' => __('sale.production_status.filter_shortage'),
            'forecast' => __('sale.production_status.filter_forecast'),
            'overdue' => __('sale.production_status.filter_overdue'),
            'cost' => __('sale.production_status.filter_cost'),
        ] as $filter => $label)
            <button type="button" class="rounded-full border border-[#dadce0] bg-white px-3 py-1.5 text-sm font-medium text-[#5f6368] transition hover:bg-[#f1f3f4] aria-pressed:border-[#1a73e8] aria-pressed:bg-[#e8f0fe] aria-pressed:text-[#174ea6]" data-production-filter="{{ $filter }}" aria-pressed="{{ $filter === 'all' ? 'true' : 'false' }}">{{ $label }}</button>
        @endforeach
    </div>
    <p class="mt-4 hidden rounded-xl border border-[#dadce0] bg-white p-6 text-center text-sm text-[#5f6368]" data-production-filter-empty>{{ __('sale.production_status.filter_empty') }}</p>

    <div class="mt-6 space-y-4">
        @forelse ($analysis['items'] as $item)
            @php([$itemStatusLabel, $itemStatusClasses] = $statusPresentation($item['production_status']))
            <details
                id="sale-line-{{ $item['line_id'] }}"
                class="group overflow-hidden rounded-2xl border border-[#dadce0] bg-white shadow-sm"
                data-production-item
                data-status="{{ $item['production_status'] }}"
                data-shortage="{{ $item['counts']['materials_short'] > 0 ? 'true' : 'false' }}"
                data-forecast="{{ $item['counts']['forecast'] > 0 ? 'true' : 'false' }}"
                data-overdue="{{ collect($item['production_orders'])->where('is_overdue', true)->isNotEmpty() ? 'true' : 'false' }}"
                data-cost-incomplete="{{ $item['costs']['estimated_incomplete'] || $item['costs']['actual_incomplete'] ? 'true' : 'false' }}"
            >
                <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-4 p-5 transition hover:bg-[#f8fafd] md:px-6 [&::-webkit-details-marker]:hidden">
                    <div class="flex min-w-0 items-center gap-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f1f3f4] text-lg transition group-open:rotate-90" aria-hidden="true">›</span>
                        <div class="min-w-0">
                            <div class="font-semibold text-[#202124]">{{ $item['sku'] }} - {{ $item['description'] }}</div>
                            <div class="mt-1 text-sm text-[#5f6368]">{{ __('sale.production_status.sold_quantity', ['quantity' => $formatQuantity($item['quantity']), 'unit' => $item['unit']]) }}</div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $itemStatusClasses }}">{{ $itemStatusLabel }}</span>
                        <span class="rounded-full border border-[#dadce0] px-3 py-1 text-xs text-[#5f6368]">{{ __('sale.production_status.orders_count', ['count' => count($item['production_orders'])]) }}</span>
                        @if ($item['counts']['materials_short'] > 0)
                            <span class="rounded-full bg-[#fce8e6] px-3 py-1 text-xs font-semibold text-[#b3261e]">{{ __('sale.production_status.material_shortages', ['count' => $item['counts']['materials_short']]) }}</span>
                        @endif
                    </div>
                </summary>

                <div class="border-t border-[#dadce0] bg-[#f8fafd] p-5 md:p-6">
                    @if ($item['missing_boms'] !== [])
                        <x-ui.alert class="mb-4" variant="warning">{{ __('sale.production_status.missing_bom') }}</x-ui.alert>
                    @endif
                    @if ($item['cycles'] !== [])
                        <x-ui.alert class="mb-4" variant="error">{{ __('sale.materials.bom_cycle') }}</x-ui.alert>
                    @endif

                    <div class="grid gap-4 lg:grid-cols-3">
                        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
                            <h2 class="text-lg font-semibold">{{ __('sale.production_status.finished_product_coverage') }}</h2>
                            @if ($item['coverage'])
                                <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                                    <div><dt class="text-[#5f6368]">{{ __('sale.materials.required') }}</dt><dd class="mt-1 font-semibold">{{ $formatQuantity($item['coverage']['required_quantity']) }} {{ $item['unit'] }}</dd></div>
                                    <div><dt class="text-[#5f6368]">{{ __('sale.materials.already_linked') }}</dt><dd class="mt-1 font-semibold text-[#137333]">{{ $formatQuantity($item['coverage']['linked_quantity']) }} {{ $item['unit'] }}</dd></div>
                                    <div><dt class="text-[#5f6368]">{{ __('sale.materials.available_to_link') }}</dt><dd class="mt-1 font-semibold text-[#137333]">{{ $formatQuantity($item['coverage']['available_to_link']) }} {{ $item['unit'] }}</dd></div>
                                    <div><dt class="text-[#5f6368]">{{ __('sale.materials.need_to_produce') }}</dt><dd class="mt-1 font-semibold {{ $item['coverage']['quantity_to_produce'] > 0 ? 'text-[#174ea6]' : 'text-[#137333]' }}">{{ $formatQuantity($item['coverage']['quantity_to_produce']) }} {{ $item['unit'] }}</dd></div>
                                </dl>
                                <div class="mt-4 border-t border-[#dadce0] pt-3 text-xs text-[#5f6368]">
                                    <div class="font-semibold">{{ __('sale.materials.warehouses') }}</div>
                                    @forelse ($item['coverage']['warehouses'] as $warehouse)
                                        <div class="mt-1">{{ $warehouse['code'] }} · {{ $formatQuantity($warehouse['quantity']) }} {{ $item['unit'] }}</div>
                                    @empty
                                        <div class="mt-1">—</div>
                                    @endforelse
                                </div>
                            @endif
                        </x-ui.panel>

                        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
                            <h2 class="text-lg font-semibold">{{ __('sale.production_status.costs') }}</h2>
                            <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                                <div><dt class="text-[#5f6368]">{{ __('sale.production_status.estimated_material') }}</dt><dd class="mt-1 font-semibold">{{ $formatMoney($item['costs']['estimated_material']) }}</dd></div>
                                <div><dt class="text-[#5f6368]">{{ __('sale.production_status.estimated_production') }}</dt><dd class="mt-1 font-semibold">{{ $formatMoney($item['costs']['estimated_production']) }}</dd></div>
                                <div><dt class="text-[#5f6368]">{{ __('sale.production_status.actual_material') }}</dt><dd class="mt-1 font-semibold">{{ $formatMoney($item['costs']['actual_material']) }}</dd></div>
                                <div><dt class="text-[#5f6368]">{{ __('sale.production_status.actual_production') }}</dt><dd class="mt-1 font-semibold">{{ $formatMoney($item['costs']['actual_production']) }}</dd></div>
                                <div class="col-span-2 border-t border-[#dadce0] pt-3">
                                    <div class="flex justify-between gap-3"><dt>{{ __('sale.production_status.estimated_total') }}</dt><dd class="font-bold">{{ $formatMoney($item['costs']['estimated_total']) }}{{ $item['costs']['estimated_incomplete'] ? '*' : '' }}</dd></div>
                                    <div class="mt-2 flex justify-between gap-3"><dt>{{ __('sale.production_status.actual_total') }}</dt><dd class="font-bold">{{ $formatMoney($item['costs']['actual_total']) }}{{ $item['costs']['actual_incomplete'] ? '*' : '' }}</dd></div>
                                </div>
                            </dl>
                        </x-ui.panel>

                        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
                            <h2 class="text-lg font-semibold">{{ __('sale.production_status.item_summary') }}</h2>
                            <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                                <div><dt class="text-[#5f6368]">{{ __('sale.production_status.completed') }}</dt><dd class="mt-1 text-2xl font-bold text-[#137333]">{{ $item['counts']['completed'] }}</dd></div>
                                <div><dt class="text-[#5f6368]">{{ __('sale.production_status.in_progress') }}</dt><dd class="mt-1 text-2xl font-bold text-[#174ea6]">{{ $item['counts']['in_progress'] }}</dd></div>
                                <div><dt class="text-[#5f6368]">{{ __('sale.production_status.planned') }}</dt><dd class="mt-1 text-2xl font-bold text-[#8a5a00]">{{ $item['counts']['planned'] }}</dd></div>
                                <div><dt class="text-[#5f6368]">{{ __('sale.production_status.forecast') }}</dt><dd class="mt-1 text-2xl font-bold text-[#7627bb]">{{ $item['counts']['forecast'] }}</dd></div>
                            </dl>
                        </x-ui.panel>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-xl border border-[#dadce0] bg-white">
                        <div class="border-b border-[#dadce0] p-4">
                            <h2 class="font-semibold">{{ __('sale.production_status.production_orders') }}</h2>
                            <p class="mt-1 text-sm text-[#5f6368]">{{ __('sale.production_status.production_orders_hint') }}</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead><tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                                    <th class="px-4 py-3">{{ __('sale.production_status.level') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.production_status.order') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.product') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.status') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.production_status.progress') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.production_status.schedule') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('sale.actions') }}</th>
                                </tr></thead>
                                <tbody>
                                    @forelse (array_merge($item['production_orders'], $item['forecasts']) as $order)
                                        @php([$orderStatusLabel, $orderStatusClasses] = $statusPresentation($order['status_group']))
                                        <tr class="border-b border-[#f1f3f4] align-top">
                                            <td class="px-4 py-4 text-[#5f6368]">{{ $order['level'] }}</td>
                                            <td class="px-4 py-4 font-medium">
                                                @if ($order['id'])
                                                    <a class="text-[#174ea6] hover:underline" href="{{ route('production.orders.show', $order['id']) }}">{{ $order['order_number'] }}</a>
                                                @else
                                                    {{ __('sale.production_status.not_created') }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-4" style="padding-left: {{ 1 + min((int) $order['level'], 5) * 0.75 }}rem">{{ $order['sku'] }} - {{ $order['description'] }}</td>
                                            <td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $orderStatusClasses }}">{{ $orderStatusLabel }}</span></td>
                                            <td class="min-w-44 px-4 py-4">
                                                <div class="h-2 overflow-hidden rounded-full bg-[#e8eaed]"><div class="h-full rounded-full bg-[#1a73e8]" style="width: {{ $order['progress_percent'] }}%"></div></div>
                                                <div class="mt-1 text-xs text-[#5f6368]">{{ $formatQuantity($order['quantity_produced']) }} / {{ $formatQuantity($order['quantity_planned']) }} {{ $order['unit'] }}</div>
                                            </td>
                                            <td class="px-4 py-4 text-xs text-[#5f6368]">{{ $order['scheduled_start'] ? \Illuminate\Support\Carbon::parse($order['scheduled_start'])->format('d/m/Y') : '—' }} → {{ $order['scheduled_end'] ? \Illuminate\Support\Carbon::parse($order['scheduled_end'])->format('d/m/Y') : '—' }}</td>
                                            <td class="px-4 py-4 text-right">
                                                @if (! $order['id'] && $capabilities['create_production_order'])
                                                    <x-ui.button
                                                        :href="route('production.orders.create', [
                                                            'sale_id' => $sale->id,
                                                            'sale_line_id' => $item['line_id'],
                                                            'product_id' => $order['product_id'],
                                                            'quantity_planned' => $order['quantity_planned'],
                                                            'dependency_level' => $order['level'],
                                                        ])"
                                                        variant="material-versions"
                                                        size="sm"
                                                        class="rounded-full whitespace-nowrap"
                                                    >{{ __('sale.production_status.create_order') }}</x-ui.button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="px-4 py-8 text-center text-[#5f6368]">{{ __('sale.production_status.no_orders') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-xl border border-[#dadce0] bg-white">
                        <div class="border-b border-[#dadce0] p-4">
                            <h2 class="font-semibold">{{ __('sale.production_status.materials') }}</h2>
                            <p class="mt-1 text-sm text-[#5f6368]">{{ __('sale.production_status.materials_hint') }}</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead><tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                                    <th class="px-4 py-3">{{ __('sale.product') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.production_status.required') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.production_status.stock') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.production_status.covered') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.materials.shortage') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.materials.recommendation') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.materials.warehouses') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('sale.actions') }}</th>
                                </tr></thead>
                                <tbody>
                                    @forelse ($item['materials'] as $material)
                                        <tr class="border-b border-[#f1f3f4]">
                                            <td class="px-4 py-4 font-medium">{{ $material['sku'] }} - {{ $material['description'] }}</td>
                                            <td class="px-4 py-4">{{ $formatQuantity($material['required_quantity']) }} {{ $material['unit'] }}</td>
                                            <td class="px-4 py-4">{{ $formatQuantity($material['stock_available']) }} {{ $material['unit'] }}</td>
                                            <td class="px-4 py-4 text-[#137333]">{{ $formatQuantity($material['linked_quantity'] + $material['available_to_link']) }} {{ $material['unit'] }}</td>
                                            <td class="px-4 py-4 font-semibold {{ $material['shortage_quantity'] > 0 ? 'text-[#b3261e]' : 'text-[#137333]' }}">{{ $formatQuantity($material['shortage_quantity']) }} {{ $material['unit'] }}</td>
                                            <td class="px-4 py-4">
                                                @if ($material['shortage_quantity'] <= 0)
                                                    {{ __('sale.materials.action_covered') }}
                                                @elseif ($material['recommended_action'] === 'BUY')
                                                    {{ __('sale.materials.action_buy') }}
                                                @else
                                                    {{ __('sale.materials.action_produce') }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 text-xs text-[#5f6368]">
                                                @forelse ($material['warehouses'] as $warehouse)
                                                    <div>{{ $warehouse['code'] }} · {{ $formatQuantity($warehouse['quantity']) }} {{ $material['unit'] }}</div>
                                                @empty
                                                    —
                                                @endforelse
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                @if ($material['shortage_quantity'] > 0 && $material['recommended_action'] === 'BUY' && $capabilities['create_purchase_requisition'])
                                                    <x-ui.button
                                                        :href="route('purchasing.requisitions.create', [
                                                            'sale_id' => $sale->id,
                                                            'sale_line_id' => $item['line_id'],
                                                            'product_id' => $material['product_id'],
                                                            'quantity' => $material['shortage_quantity'],
                                                            'warehouse_id' => data_get($material, 'warehouses.0.id'),
                                                        ])"
                                                        variant="material-versions"
                                                        size="sm"
                                                        class="rounded-full whitespace-nowrap"
                                                    >{{ __('sale.production_status.create_requisition') }}</x-ui.button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="px-4 py-8 text-center text-[#5f6368]">{{ __('sale.production_status.no_materials') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </details>
        @empty
            <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-8">
                <p class="text-center text-[#5f6368]">{{ __('sale.empty_items') }}</p>
            </x-ui.panel>
        @endforelse
    </div>

    @if ($analysis['timeline'] !== [])
        <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
            <h2 class="text-lg font-semibold">{{ __('sale.production_status.timeline') }}</h2>
            <ol class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($analysis['timeline'] as $event)
                    <li class="relative rounded-xl border border-[#dadce0] bg-[#f8fafd] p-4 pl-5">
                        <span class="absolute bottom-4 left-0 top-4 w-1 rounded-r-full bg-[#1a73e8]" aria-hidden="true"></span>
                        <div class="text-xs text-[#5f6368]">{{ \Illuminate\Support\Carbon::parse($event['date'])->format('d/m/Y H:i') }}</div>
                        <div class="mt-1 text-sm font-semibold">{{ __('sale.production_status.timeline_events.'.$event['type'], ['order' => $event['order_number'] ?? '']) }}</div>
                    </li>
                @endforeach
            </ol>
        </x-ui.panel>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const items = Array.from(document.querySelectorAll('[data-production-item]'));
    const filters = document.querySelectorAll('[data-production-filter]');
    const emptyState = document.querySelector('[data-production-filter-empty]');

    const matchesFilter = (item, filter) => {
        if (filter === 'all') return true;
        if (filter === 'attention') return item.dataset.shortage === 'true' || item.dataset.forecast === 'true' || item.dataset.overdue === 'true' || item.dataset.costIncomplete === 'true';
        if (filter === 'shortage') return item.dataset.shortage === 'true';
        if (filter === 'forecast') return item.dataset.forecast === 'true';
        if (filter === 'overdue') return item.dataset.overdue === 'true';
        if (filter === 'cost') return item.dataset.costIncomplete === 'true';
        return true;
    };

    filters.forEach((button) => button.addEventListener('click', () => {
        const filter = button.dataset.productionFilter;
        let visible = 0;

        filters.forEach((candidate) => candidate.setAttribute('aria-pressed', candidate === button ? 'true' : 'false'));
        items.forEach((item) => {
            const matches = matchesFilter(item, filter);
            item.classList.toggle('hidden', !matches);
            if (matches) visible += 1;
        });
        emptyState?.classList.toggle('hidden', visible > 0);
    }));

    document.querySelectorAll('[data-alert-line]').forEach((alert) => alert.addEventListener('click', () => {
        const lineId = alert.dataset.alertLine;
        if (!lineId) return;
        const item = document.getElementById(`sale-line-${lineId}`);
        if (!item) return;
        item.classList.remove('hidden');
        item.open = true;
        item.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }));
});
</script>
@endsection
