@extends('layouts.client-area')

@section('title', __('sale.production_status.title'))
@section('client-page-title', __('sale.production_status.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    @if (session('status'))
        <x-ui.alert class="mb-4" variant="success">{{ session('status') }}</x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.alert class="mb-4" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif
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
            'blocked_materials' => [__('sale.production_status.readiness_blocked_materials'), 'bg-[#fce8e6] text-[#b3261e]', 'bg-[#d93025]'],
            'at_risk' => [__('sale.production_status.readiness_at_risk'), 'bg-[#fce8e6] text-[#b3261e]', 'bg-[#d93025]'],
            default => [__('sale.production_status.readiness_unscheduled'), 'bg-[#fef7e0] text-[#8a5a00]', 'bg-[#f9ab00]'],
        };
    @endphp

    <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-[#5f6368]">{{ __('sale.reference_label', ['id' => $sale->id]) }}</p>
        <div class="mt-1 flex flex-wrap items-center justify-between gap-4">
            <h1 class="font-display text-3xl font-bold">{{ __('sale.production_status.title') }}</h1>
            <div class="flex flex-wrap items-center justify-end gap-1 print:hidden" role="group" aria-label="{{ __('sale.production_status.report_actions') }}">
                <a class="ui-icon-button border border-[#dadce0] bg-white" href="{{ route('sales.production-status.export', [$sale, 'xlsx']) }}" title="{{ __('sale.production_status.export_excel') }}" aria-label="{{ __('sale.production_status.export_excel') }}">
                    <x-ui.icon name="chart-bar" />
                </a>
                <a class="ui-icon-button border border-[#dadce0] bg-white" href="{{ route('sales.production-status.export', [$sale, 'pdf']) }}" title="{{ __('sale.production_status.export_pdf') }}" aria-label="{{ __('sale.production_status.export_pdf') }}">
                    <x-ui.icon name="receipt" />
                </a>
                <button type="button" class="ui-icon-button border border-[#dadce0] bg-white" title="{{ __('sale.production_status.print') }}" aria-label="{{ __('sale.production_status.print') }}" onclick="window.print()">
                    <x-ui.icon name="printer" />
                </button>
                <button type="button" class="ui-icon-button border border-[#dadce0] bg-white" title="{{ __('sale.production_status.share') }}" aria-label="{{ __('sale.production_status.share') }}" data-share-report>
                    <x-ui.icon name="share" />
                </button>
                <a class="ui-icon-button border border-[#dadce0] bg-white" href="{{ route('sales.production-status', $sale) }}" title="{{ __('sale.production_status.refresh') }}" aria-label="{{ __('sale.production_status.refresh') }}">
                    <x-ui.icon name="refresh" />
                </a>
                <span class="mx-2 h-6 w-px bg-[#dadce0]" aria-hidden="true"></span>
                <x-ui.button :href="route('sales.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            </div>
        </div>
        <p class="mt-2 text-sm text-[#5f6368]">{{ __('sale.production_status.subtitle', ['customer' => $sale->customer?->name ?? __('sale.customer_removed')]) }}</p>
    </div>

    <x-ui.tabs class="mt-6" :label="__('sale.production_status.tabs_label')" data-production-tabs>
        @php
            $productionTabs = [
                'summary' => ['icon' => 'layout-dashboard', 'metric' => number_format($analysis['progress_percent'], 0, ',', '.').'%', 'active' => true],
                'items' => ['icon' => 'package', 'metric' => count($analysis['items']), 'active' => false],
                'timeline' => ['icon' => 'calendar', 'metric' => count($analysis['timeline']), 'active' => false],
                'tracking' => ['icon' => 'users', 'metric' => count($analysis['tracking']['comments']), 'active' => false],
            ];
        @endphp
        <x-ui.tabs.list class="production-status-tabs-list print:hidden">
            @foreach ($productionTabs as $tab => $presentation)
                <x-ui.tabs.tab
                    id="production-status-tab-{{ $tab }}"
                    target="production-status-panel-{{ $tab }}"
                    :active="$presentation['active']"
                    class="production-status-tab"
                    data-production-tab-name="{{ $tab }}"
                >
                    <span class="production-status-tab-icon"><x-ui.icon :name="$presentation['icon']" /></span>
                    <span class="min-w-0 flex-1 text-left">
                        <span class="production-status-tab-title">{{ __('sale.production_status.tab_'.$tab) }}</span>
                        <span class="production-status-tab-description">{{ __('sale.production_status.tab_'.$tab.'_description') }}</span>
                    </span>
                    <span class="production-status-tab-metric" aria-hidden="true">{{ $presentation['metric'] }}</span>
                    <span class="sr-only">{{ __('sale.production_status.tab_'.$tab.'_metric', ['count' => $presentation['metric']]) }}</span>
                </x-ui.tabs.tab>
            @endforeach
        </x-ui.tabs.list>

        <x-ui.tabs.panel id="production-status-panel-summary" labelledby="production-status-tab-summary" :active="true">
    <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr] lg:items-center">
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
        </div>
    </x-ui.panel>

    <x-ui.panel class="mt-4 border-[#dadce0] shadow-none" padding="p-5">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach ([
                ['promised_date', $analysis['schedule']['promised_date']],
                ['planned_start', $analysis['schedule']['planned_start']],
                ['planned_end', $analysis['schedule']['planned_end']],
                ['projected_completion', $analysis['schedule']['projected_completion']],
            ] as [$label, $date])
                <div><div class="text-xs text-[#5f6368]">{{ __('sale.production_status.'.$label) }}</div><div class="mt-1 font-semibold">{{ $date ? \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') : '—' }}</div></div>
            @endforeach
            <div><div class="text-xs text-[#5f6368]">{{ __('sale.production_status.days_late') }}</div><div class="mt-1 font-semibold {{ $analysis['schedule']['days_late'] > 0 ? 'text-[#b3261e]' : 'text-[#137333]' }}">{{ $analysis['schedule']['days_late'] }}</div></div>
            <div><div class="text-xs text-[#5f6368]">{{ __('sale.production_status.limiting_material') }}</div><div class="mt-1 font-semibold">{{ data_get($analysis, 'schedule.limiting_material.sku', '—') }}</div></div>
        </div>
        @if ($analysis['schedule']['critical_path'])
            <div class="mt-4 border-t border-[#dadce0] pt-3 text-sm text-[#5f6368]">{{ __('sale.production_status.critical_path') }}:
                @if ($capabilities['read_production_order'])
                    <a class="font-semibold text-[#174ea6] hover:underline" href="{{ route('production.orders.show', $analysis['schedule']['critical_path']['order_id']) }}">{{ $analysis['schedule']['critical_path']['order_number'] }} · {{ $analysis['schedule']['critical_path']['product'] }}</a>
                @else
                    <strong>{{ $analysis['schedule']['critical_path']['order_number'] }} · {{ $analysis['schedule']['critical_path']['product'] }}</strong>
                @endif
            </div>
        @endif
    </x-ui.panel>

    <label class="mt-4 inline-flex items-center gap-2 text-sm text-[#5f6368] print:hidden"><input type="checkbox" class="rounded" data-auto-refresh> {{ __('sale.production_status.auto_refresh') }}</label>

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

    <x-ui.panel class="mt-4 border-[#dadce0] shadow-none" padding="p-5">
        <h2 class="font-semibold">{{ __('sale.production_status.cost_breakdown') }}</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
            @foreach ([
                ['estimated_material', $analysis['costs']['estimated_material']],
                ['estimated_labor', $analysis['costs']['estimated_labor']],
                ['estimated_machine', $analysis['costs']['estimated_machine']],
                ['actual_material', $analysis['costs']['actual_material']],
                ['actual_labor', $analysis['costs']['actual_labor']],
                ['actual_machine', $analysis['costs']['actual_machine']],
                ['actual_scrap', $analysis['costs']['actual_scrap']],
            ] as [$label, $amount])
                <div><div class="text-xs text-[#5f6368]">{{ __('sale.production_status.'.$label) }}</div><div class="mt-1 font-semibold">{{ $formatMoney($amount) }}</div></div>
            @endforeach
        </div>
        <div class="mt-4 grid gap-4 border-t border-[#dadce0] pt-4 sm:grid-cols-3">
            <div><div class="text-xs text-[#5f6368]">{{ __('sale.production_status.sales_amount') }}</div><div class="mt-1 font-bold">{{ $formatMoney($analysis['costs']['sales_amount']) }}</div></div>
            <div><div class="text-xs text-[#5f6368]">{{ __('sale.production_status.estimated_margin') }}</div><div class="mt-1 font-bold {{ $analysis['costs']['estimated_margin'] >= 0 ? 'text-[#137333]' : 'text-[#b3261e]' }}">{{ $formatMoney($analysis['costs']['estimated_margin']) }}</div></div>
            <div><div class="text-xs text-[#5f6368]">{{ __('sale.production_status.estimated_margin_percent') }}</div><div class="mt-1 font-bold">{{ $analysis['costs']['estimated_margin_percent'] !== null ? number_format($analysis['costs']['estimated_margin_percent'], 1, ',', '.').'%' : '—' }}</div></div>
        </div>
    </x-ui.panel>

        </x-ui.tabs.panel>

        @foreach (['items', 'timeline', 'tracking'] as $tab)
            <x-ui.tabs.panel
                id="production-status-panel-{{ $tab }}"
                labelledby="production-status-tab-{{ $tab }}"
                data-production-tab-panel="{{ $tab }}"
                data-production-tab-url="{{ route('sales.production-status.tab', [$sale, $tab]) }}"
            >
                <div class="rounded-xl border border-[#dadce0] bg-white p-8 text-center text-sm text-[#5f6368]" data-production-tab-placeholder>
                    {{ __('sale.production_status.tab_select_hint') }}
                </div>
            </x-ui.tabs.panel>
        @endforeach
    </x-ui.tabs>

    @if (($productionStatusTab ?? null) === 'items')
    @fragment('production-status-items')
    <div class="mt-6 flex flex-wrap items-center gap-2" role="group" aria-label="{{ __('sale.production_status.filters') }}">
        @foreach ([
            'all' => __('sale.production_status.filter_all'),
            'shortage' => __('sale.production_status.filter_shortage'),
            'overdue' => __('sale.production_status.filter_overdue'),
            'in_progress' => __('sale.production_status.filter_in_progress'),
            'forecast' => __('sale.production_status.filter_forecast'),
            'cost' => __('sale.production_status.filter_cost'),
            'structure_rate' => __('sale.production_status.filter_structure_rate'),
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
                data-in-progress="{{ $item['counts']['in_progress'] > 0 ? 'true' : 'false' }}"
                data-cost-incomplete="{{ $item['costs']['estimated_incomplete'] || $item['costs']['actual_incomplete'] ? 'true' : 'false' }}"
                data-structure-rate="{{ $item['missing_boms'] !== [] || collect($item['production_orders'])->contains('missing_routing', true) || collect($item['production_orders'])->flatMap(fn ($order) => data_get($order, 'costs.rate_evidence', []))->contains('rate', null) ? 'true' : 'false' }}"
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
                                <div><dt class="text-[#5f6368]">{{ __('sale.production_status.estimated_per_unit') }}</dt><dd class="mt-1 font-semibold">{{ $item['costs']['estimated_per_unit'] !== null ? $formatMoney($item['costs']['estimated_per_unit']) : '—' }}</dd></div>
                                <div><dt class="text-[#5f6368]">{{ __('sale.production_status.actual_per_unit') }}</dt><dd class="mt-1 font-semibold">{{ $item['costs']['actual_per_unit'] !== null ? $formatMoney($item['costs']['actual_per_unit']) : '—' }}</dd></div>
                                <div><dt class="text-[#5f6368]">{{ __('sale.production_status.cost_variance') }}</dt><dd class="mt-1 font-semibold">{{ $formatMoney($item['costs']['variance']) }}</dd></div>
                                <div><dt class="text-[#5f6368]">{{ __('sale.production_status.variance_percent') }}</dt><dd class="mt-1 font-semibold">{{ $item['costs']['variance_percent'] !== null ? number_format($item['costs']['variance_percent'], 1, ',', '.').'%' : '—' }}</dd></div>
                                <div class="col-span-2 border-t border-[#dadce0] pt-3">
                                    <div class="flex justify-between gap-3"><dt>{{ __('sale.production_status.estimated_total') }}</dt><dd class="font-bold">{{ $formatMoney($item['costs']['estimated_total']) }}{{ $item['costs']['estimated_incomplete'] ? '*' : '' }}</dd></div>
                                    <div class="mt-2 flex justify-between gap-3"><dt>{{ __('sale.production_status.actual_total') }}</dt><dd class="font-bold">{{ $formatMoney($item['costs']['actual_total']) }}{{ $item['costs']['actual_incomplete'] ? '*' : '' }}</dd></div>
                                </div>
                            </dl>
                            <details class="mt-4 border-t border-[#dadce0] pt-3 text-xs">
                                <summary class="cursor-pointer font-semibold text-[#174ea6]">{{ __('sale.production_status.cost_evidence') }}</summary>
                                <div class="mt-2 space-y-1 text-[#5f6368]">
                                    @foreach ($item['costs']['evidence']['materials'] as $evidence)
                                        <div>{{ $evidence['sku'] }} · {{ $evidence['unit_cost'] !== null ? $formatMoney($evidence['unit_cost']) : __('sale.production_status.no_price') }} · {{ $evidence['reference'] ?? '—' }}</div>
                                    @endforeach
                                    @foreach ($item['costs']['evidence']['rates'] as $evidence)
                                        <div>{{ $evidence['work_center'] ?? '—' }} · {{ $evidence['rate'] !== null ? $formatMoney($evidence['rate']).'/h' : __('sale.production_status.no_rate') }} · {{ $evidence['date'] }}</div>
                                    @endforeach
                                </div>
                            </details>
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

                    <div class="mt-6 rounded-xl border border-[#dadce0] bg-white p-4">
                        <h2 class="font-semibold">{{ __('sale.production_status.product_tree') }}</h2>
                        <p class="mt-1 text-sm text-[#5f6368]">{{ __('sale.production_status.product_tree_hint') }}</p>
                        <ul class="mt-4 space-y-2">
                            @include('client.sales._production-tree-node', ['node' => $item['tree']])
                        </ul>
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
                                                @if ($order['id'] && $capabilities['read_production_order'])
                                                    <a class="text-[#174ea6] hover:underline" href="{{ route('production.orders.show', $order['id']) }}">{{ $order['order_number'] }}</a>
                                                @elseif ($order['id'])
                                                    {{ $order['order_number'] }}
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
                                                @if ($order['id'] && $capabilities['read_production_order'])
                                                    <a class="ml-2 inline-flex rounded-full border border-[#dadce0] px-3 py-1.5 text-xs font-semibold text-[#174ea6]" href="{{ route('production.orders.show', $order['id']) }}">{{ __('sale.production_status.open_order') }}</a>
                                                @endif
                                                @if ($order['id'] && $capabilities['reschedule_production_order'] && ! in_array($order['status'], ['COMPLETED', 'CANCELLED'], true))
                                                    <details class="relative mt-2 inline-block text-left">
                                                        <summary class="cursor-pointer list-none rounded-full border border-[#dadce0] px-3 py-1.5 text-xs font-semibold text-[#174ea6]">{{ __('sale.production_status.reschedule') }}</summary>
                                                        <form method="POST" action="{{ route('production.orders.reschedule', $order['id']) }}" class="absolute right-0 z-20 mt-2 w-72 space-y-3 rounded-xl border border-[#dadce0] bg-white p-4 text-left shadow-xl">
                                                            @csrf
                                                            <label class="block text-xs">{{ __('sale.production_status.planned_start') }}<input class="mt-1 w-full rounded-lg border border-[#dadce0] p-2" type="date" name="scheduled_start_date" value="{{ $order['scheduled_start'] }}" required></label>
                                                            <label class="block text-xs">{{ __('sale.production_status.planned_end') }}<input class="mt-1 w-full rounded-lg border border-[#dadce0] p-2" type="date" name="scheduled_end_date" value="{{ $order['scheduled_end'] }}" required></label>
                                                            <button class="w-full rounded-full bg-[#1a73e8] px-3 py-2 text-xs font-semibold text-white" type="submit">{{ __('sale.production_status.save_schedule') }}</button>
                                                        </form>
                                                    </details>
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
                                    <th class="px-4 py-3">{{ __('sale.production_status.reserved_quantity') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.production_status.available_stock') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.production_status.in_production') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.production_status.in_purchase') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.production_status.received') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.production_status.net_shortage') }}</th>
                                    <th class="px-4 py-3">{{ __('sale.production_status.unit_cost') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('sale.actions') }}</th>
                                </tr></thead>
                                <tbody>
                                    @forelse ($item['materials'] as $material)
                                        <tr class="border-b border-[#f1f3f4]">
                                            <td class="px-4 py-4 font-medium">{{ $material['sku'] }} - {{ $material['description'] }}</td>
                                            <td class="px-4 py-4">{{ $formatQuantity($material['required_quantity']) }} {{ $material['unit'] }}</td>
                                            <td class="px-4 py-4 text-[#137333]">{{ $formatQuantity($material['reserved_quantity']) }}</td>
                                            <td class="px-4 py-4">{{ $formatQuantity($material['available_quantity']) }}</td>
                                            <td class="px-4 py-4">{{ $formatQuantity($material['in_production']) }}</td>
                                            <td class="px-4 py-4">{{ $formatQuantity($material['in_purchase']) }}</td>
                                            <td class="px-4 py-4">{{ $formatQuantity($material['received_quantity']) }}</td>
                                            <td class="px-4 py-4 font-semibold {{ $material['net_shortage'] > 0 ? 'text-[#b3261e]' : 'text-[#137333]' }}">{{ $formatQuantity($material['net_shortage']) }}</td>
                                            <td class="px-4 py-4 text-xs">{{ $material['unit_cost'] !== null ? $formatMoney($material['unit_cost']) : '—' }}<div class="mt-1 text-[#5f6368]">{{ $material['cost_reference'] ?? '' }}</div></td>
                                            <td class="px-4 py-4 text-right">
                                                <div class="flex min-w-44 flex-col items-end gap-2">
                                                @if ($material['net_shortage'] > 0 && $material['recommended_action'] === 'BUY' && $capabilities['create_purchase_requisition'])
                                                    <x-ui.button
                                                        :href="route('purchasing.requisitions.create', [
                                                            'sale_id' => $sale->id,
                                                            'sale_line_id' => $item['line_id'],
                                                            'product_id' => $material['product_id'],
                                                            'quantity' => $material['net_shortage'],
                                                            'warehouse_id' => data_get($material, 'warehouses.0.id'),
                                                        ])"
                                                        variant="material-versions"
                                                        size="sm"
                                                        class="rounded-full whitespace-nowrap"
                                                    >{{ __('sale.production_status.create_requisition') }}</x-ui.button>
                                                @endif
                                                @if ($material['available_quantity'] > 0 && $capabilities['reserve_stock'] && data_get($material, 'warehouses.0.id'))
                                                    <form method="POST" action="{{ route('sales.production-status.reserve', $sale) }}">
                                                        @csrf
                                                        <input type="hidden" name="sale_line_id" value="{{ $item['line_id'] }}">
                                                        <input type="hidden" name="product_id" value="{{ $material['product_id'] }}">
                                                        <input type="hidden" name="warehouse_id" value="{{ data_get($material, 'warehouses.0.id') }}">
                                                        <input type="hidden" name="quantity" value="{{ min($material['available_quantity'], (float) data_get($material, 'warehouses.0.quantity', 0)) }}">
                                                        <button class="rounded-full border border-[#dadce0] px-3 py-1.5 text-xs font-semibold text-[#174ea6]" type="submit">{{ __('sale.production_status.reserve_stock') }}</button>
                                                    </form>
                                                @endif
                                                @if ($capabilities['read_purchase_order'])
                                                    @foreach ($material['purchase_orders'] as $purchaseOrder)
                                                        <a class="text-xs font-semibold text-[#174ea6] hover:underline" href="{{ route('purchasing.orders.show', $purchaseOrder['id']) }}">{{ __('sale.production_status.open_purchase_order') }} {{ $purchaseOrder['number'] }}</a>
                                                    @endforeach
                                                @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="10" class="px-4 py-8 text-center text-[#5f6368]">{{ __('sale.production_status.no_materials') }}</td></tr>
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
    @endfragment
    @endif

    @if (($productionStatusTab ?? null) === 'timeline')
    @fragment('production-status-timeline')
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
    @else
        <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-8">
            <p class="text-center text-[#5f6368]">{{ __('sale.production_status.timeline_empty') }}</p>
        </x-ui.panel>
    @endif
    @endfragment
    @endif

    @if (($productionStatusTab ?? null) === 'tracking')
    @fragment('production-status-tracking')
    <div class="mt-6 grid gap-6 lg:grid-cols-2 print:hidden">
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5 md:p-6">
            <h2 class="text-lg font-semibold">{{ __('sale.production_status.comments_responsible') }}</h2>
            @if ($capabilities['manage_tracking'])
                <form method="POST" action="{{ route('sales.production-status.tracking', $sale) }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="text-sm">{{ __('sale.production_status.promised_date') }}<input class="mt-1 w-full rounded-xl border border-[#dadce0] p-2.5" type="date" name="promised_date" value="{{ old('promised_date', $analysis['tracking']['promised_date']) }}"></label>
                        <label class="text-sm">{{ __('sale.production_status.responsible') }}
                            <select class="mt-1 w-full rounded-xl border border-[#dadce0] p-2.5" name="responsible_user_id">
                                <option value="">{{ __('sale.production_status.no_responsible') }}</option>
                                @foreach ($responsibleUsers as $responsible)
                                    <option value="{{ $responsible->id }}" @selected((int) old('responsible_user_id', $analysis['tracking']['responsible_user_id']) === (int) $responsible->id)>{{ $responsible->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <label class="block text-sm">{{ __('sale.production_status.new_comment') }}<textarea class="mt-1 w-full rounded-xl border border-[#dadce0] p-3" name="comment" rows="3" maxlength="2000"></textarea></label>
                    <button class="rounded-full bg-[#1a73e8] px-4 py-2 text-sm font-semibold text-white" type="submit">{{ __('sale.production_status.save_tracking') }}</button>
                </form>
            @else
                <p class="mt-3 text-sm text-[#5f6368]">{{ $analysis['tracking']['responsible_name'] ?? __('sale.production_status.no_responsible') }}</p>
            @endif
            <div class="mt-5 space-y-3 border-t border-[#dadce0] pt-4">
                @forelse ($analysis['tracking']['comments'] as $comment)
                    <article class="rounded-xl bg-[#f8fafd] p-3"><div class="text-xs text-[#5f6368]">{{ $comment['user_name'] ?? '—' }} · {{ \Illuminate\Support\Carbon::parse($comment['created_at'])->format('d/m/Y H:i') }}</div><p class="mt-1 whitespace-pre-line text-sm">{{ $comment['text'] }}</p></article>
                @empty
                    <p class="text-sm text-[#5f6368]">{{ __('sale.production_status.no_comments') }}</p>
                @endforelse
            </div>
        </x-ui.panel>

        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5 md:p-6">
            <h2 class="text-lg font-semibold">{{ __('sale.production_status.change_history') }}</h2>
            <ol class="mt-4 space-y-3">
                @forelse ($analysis['history'] as $history)
                    <li class="border-l-2 border-[#1a73e8] pl-3"><div class="text-xs text-[#5f6368]">{{ $history['date'] ? \Illuminate\Support\Carbon::parse($history['date'])->format('d/m/Y H:i') : '—' }}</div><div class="mt-1 text-sm font-medium">{{ __('sale.production_status.history_events.'.str_replace('.', '_', $history['event'])) }}</div></li>
                @empty
                    <li class="text-sm text-[#5f6368]">{{ __('sale.production_status.no_history') }}</li>
                @endforelse
            </ol>
        </x-ui.panel>
    </div>
    @endfragment
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabsRoot = document.querySelector('[data-production-tabs]');

    const initializeProductionItems = (root) => {
        const items = Array.from(root.querySelectorAll('[data-production-item]'));
        const filters = Array.from(root.querySelectorAll('[data-production-filter]'));
        const emptyState = root.querySelector('[data-production-filter-empty]');
        const matchesFilter = (item, filter) => {
            if (filter === 'all') return true;
            if (filter === 'shortage') return item.dataset.shortage === 'true';
            if (filter === 'forecast') return item.dataset.forecast === 'true';
            if (filter === 'overdue') return item.dataset.overdue === 'true';
            if (filter === 'in_progress') return item.dataset.inProgress === 'true';
            if (filter === 'cost') return item.dataset.costIncomplete === 'true';
            if (filter === 'structure_rate') return item.dataset.structureRate === 'true';
            return true;
        };
        const applyFilter = (filter) => {
            let visible = 0;

            filters.forEach((candidate) => candidate.setAttribute('aria-pressed', candidate.dataset.productionFilter === filter ? 'true' : 'false'));
            items.forEach((item) => {
                const matches = matchesFilter(item, filter);
                item.classList.toggle('hidden', !matches);
                if (matches) visible += 1;
            });
            emptyState?.classList.toggle('hidden', visible > 0);
        };

        filters.forEach((button) => button.addEventListener('click', () => {
            const filter = button.dataset.productionFilter;
            const url = new URL(window.location.href);
            filter === 'all' ? url.searchParams.delete('filter') : url.searchParams.set('filter', filter);
            window.history.replaceState({}, '', url);
            applyFilter(filter);
        }));

        const initialFilter = new URL(window.location.href).searchParams.get('filter') || 'all';
        applyFilter(filters.some((button) => button.dataset.productionFilter === initialFilter) ? initialFilter : 'all');
    };

    const loadProductionTab = async (button) => {
        const tab = button.dataset.productionTabName;
        const panel = document.getElementById(button.getAttribute('aria-controls'));
        if (!panel || tab === 'summary' || panel.dataset.loaded === 'true' || panel.dataset.loading === 'true') return;

        panel.dataset.loading = 'true';
        panel.setAttribute('aria-busy', 'true');
        panel.innerHTML = `<div class="rounded-xl border border-[#dadce0] bg-white p-8 text-center text-sm text-[#5f6368]">${@json(__('sale.production_status.tab_loading'))}</div>`;

        try {
            const response = await fetch(panel.dataset.productionTabUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            panel.innerHTML = await response.text();
            panel.dataset.loaded = 'true';
            if (tab === 'items') initializeProductionItems(panel);
        } catch (error) {
            const errorBox = document.createElement('div');
            errorBox.className = 'rounded-xl border border-[#f6aea9] bg-[#fce8e6] p-8 text-center text-sm text-[#b3261e]';
            const message = document.createElement('p');
            message.textContent = @json(__('sale.production_status.tab_load_error'));
            const retry = document.createElement('button');
            retry.type = 'button';
            retry.className = 'mt-3 rounded-full border border-[#b3261e] px-4 py-2 font-semibold';
            retry.textContent = @json(__('sale.production_status.tab_retry'));
            retry.addEventListener('click', () => loadProductionTab(button));
            errorBox.append(message, retry);
            panel.replaceChildren(errorBox);
        } finally {
            delete panel.dataset.loading;
            panel.removeAttribute('aria-busy');
        }
    };

    if (tabsRoot) {
        const tabButtons = Array.from(tabsRoot.querySelectorAll('[data-production-tab-name]'));
        tabButtons.forEach((button) => button.addEventListener('click', () => {
            const tab = button.dataset.productionTabName;
            const url = new URL(window.location.href);
            tab === 'summary' ? url.searchParams.delete('tab') : url.searchParams.set('tab', tab);
            if (tab !== 'items') url.searchParams.delete('filter');
            window.history.replaceState({}, '', url);
            loadProductionTab(button);
        }));

        const requestedTab = new URL(window.location.href).searchParams.get('tab');
        const initialTab = tabButtons.find((button) => button.dataset.productionTabName === requestedTab);
        if (initialTab) initialTab.click();
    }

    document.querySelector('[data-share-report]')?.addEventListener('click', async (event) => {
        await navigator.clipboard.writeText(window.location.href);
        const originalTitle = event.currentTarget.title;
        const originalLabel = event.currentTarget.getAttribute('aria-label');
        event.currentTarget.title = @json(__('sale.production_status.link_copied'));
        event.currentTarget.setAttribute('aria-label', @json(__('sale.production_status.link_copied')));
        window.setTimeout(() => {
            event.currentTarget.title = originalTitle;
            event.currentTarget.setAttribute('aria-label', originalLabel);
        }, 1800);
    });

    const autoRefresh = document.querySelector('[data-auto-refresh]');
    if (autoRefresh) {
        autoRefresh.checked = window.localStorage.getItem('sale-production-auto-refresh') === '1';
        autoRefresh.addEventListener('change', () => window.localStorage.setItem('sale-production-auto-refresh', autoRefresh.checked ? '1' : '0'));
        window.setInterval(() => { if (autoRefresh.checked) window.location.reload(); }, 300000);
    }

});
</script>
@endsection
