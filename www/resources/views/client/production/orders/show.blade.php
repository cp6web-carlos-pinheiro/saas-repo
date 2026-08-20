@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.$order->order_number)
@section('client-page-title', $order->order_number)

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $order->order_number }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('production.analytics.index')" variant="secondary" class="rounded-full">{{ __('production.orders.analytics') }}</x-ui.button>
            <x-ui.button :href="route('production.orders.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('production.status')">{{ __('production.status_labels.'.$order->status) }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.orders.sale_number')">{{ $order->sales_order_reference ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.product')">{{ $order->product?->sku }} - {{ $order->product?->description }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.warehouse')">{{ $order->warehouse?->code }} - {{ $order->warehouse?->name }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.orders.planned_quantity')">{{ number_format((float) $order->quantity_planned, 3, ',', '.') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.orders.good_quantity')">{{ number_format((float) $order->quantity_produced, 3, ',', '.') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.orders.scrap_quantity')">{{ number_format((float) $order->quantity_scrapped, 3, ',', '.') }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('production.orders.scheduled_start')" :value="$order->scheduled_start_date" />
            <x-ui.definition-item-date :label="__('production.orders.scheduled_end')" :value="$order->scheduled_end_date" />
        </x-ui.definition-grid>

        <div class="mt-6 flex flex-wrap gap-3">
            @if ($order->status === 'DRAFT')
                <form method="POST" action="{{ route('production.orders.release', $order) }}">
                    @csrf
                    <x-ui.button type="submit" variant="primary" class="rounded-full">{{ __('production.orders.release') }}</x-ui.button>
                </form>
            @endif

            @if (! in_array($order->status, ['COMPLETED', 'CANCELLED'], true))
                <form method="POST" action="{{ route('production.orders.complete', $order) }}">
                    @csrf
                    <x-ui.button type="submit" variant="secondary" class="rounded-full">{{ __('production.orders.complete') }}</x-ui.button>
                </form>
            @endif
        </div>
    </x-ui.panel>

    @if ($order->status !== 'COMPLETED')
    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">{{ __('production.orders.posting_title') }}</h2>
            <p class="mt-1 text-sm text-[var(--ui-text-muted)]">{{ __('production.orders.posting_hint') }}</p>

            <form class="mt-4 space-y-4" method="POST" action="{{ route('production.orders.outputs.store', $order) }}">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium">{{ __('production.orders.good_quantity') }}
                        <x-ui.input class="mt-2" type="number" step="0.001" min="0" name="quantity_completed" value="0" required />
                    </label>
                    <label class="block text-sm font-medium">{{ __('production.orders.scrap_quantity') }}
                        <x-ui.input class="mt-2" type="number" step="0.001" min="0" name="quantity_scrapped" value="0" />
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium">{{ __('production.operation') }}
                        <x-ui.input class="mt-2" type="number" min="1" name="operation_no" />
                    </label>
                    <label class="block text-sm font-medium">{{ __('production.orders.finished_lot') }}
                        <x-ui.input class="mt-2" name="lot_number" maxlength="120" />
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium">{{ __('production.orders.setup_time') }}
                        <x-ui.input class="mt-2" type="text" inputmode="numeric" name="setup_time_minutes" :value="old('setup_time_minutes', '00:00')" placeholder="00:00" data-duration-mask="true" />
                    </label>
                    <label class="block text-sm font-medium">{{ __('production.orders.process_time') }}
                        <x-ui.input class="mt-2" type="text" inputmode="numeric" name="process_time_minutes" :value="old('process_time_minutes', '00:00')" placeholder="00:00" data-duration-mask="true" />
                    </label>
                </div>

                <label class="block text-sm font-medium">{{ __('production.orders.inspection_status') }}
                    <x-ui.select class="mt-2" name="inspection_status" data-search="off">
                        @foreach (['APPROVED', 'PENDING', 'REJECTED'] as $inspectionStatus)
                            <option value="{{ $inspectionStatus }}">{{ __('production.status_labels.'.$inspectionStatus) }}</option>
                        @endforeach
                    </x-ui.select>
                </label>

                <x-ui.button type="submit" variant="primary" class="rounded-full">{{ __('production.orders.posting_title') }}</x-ui.button>
            </form>
        </x-ui.panel>

        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">{{ __('production.orders.consumption_title') }}</h2>
            <p class="mt-1 text-sm text-[var(--ui-text-muted)]">{{ __('production.orders.consumption_hint') }}</p>

            <div class="mt-4 space-y-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-[var(--ui-text-muted)]">{{ __('production.orders.planned_products') }}</h3>
                @forelse ($plannedMaterials as $plannedMaterial)
                    @php($component = $plannedMaterial['component'])
                    <form class="rounded-xl border border-[var(--ui-border)] p-3" method="POST" action="{{ route('production.orders.consumptions.store', $order) }}">
                        @csrf
                        <x-ui.input type="hidden" name="product_id" :value="$component->component_product_id" unstyled />
                        <x-ui.input type="hidden" name="reference_bom_component_id" :value="$component->id" unstyled />
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="font-medium">{{ $component->componentProduct->sku }} - {{ $component->componentProduct->description }}</div>
                                <div class="text-xs text-[var(--ui-text-muted)]">{{ __('production.orders.planned_consumed_balance', ['planned' => number_format($plannedMaterial['planned_quantity'], 3, ',', '.'), 'consumed' => number_format($plannedMaterial['consumed_quantity'], 3, ',', '.'), 'balance' => number_format($plannedMaterial['remaining_quantity'], 3, ',', '.')]) }}</div>
                            </div>
                            <div class="flex items-end gap-2">
                                <label class="text-xs text-[var(--ui-text-muted)]">{{ __('production.orders.confirm_quantity') }}
                                    <x-ui.input class="mt-1 w-32" type="number" step="0.001" min="0.001" name="quantity_consumed" value="{{ $plannedMaterial['remaining_quantity'] > 0 ? $plannedMaterial['remaining_quantity'] : '' }}" required />
                                </label>
                                <label class="text-xs text-[var(--ui-text-muted)]">{{ __('production.warehouse') }}
                                    <x-ui.select class="mt-1 w-40" name="warehouse_id" required data-search="off">
                                        <option value="">{{ __('production.select') }}</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->code }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </label>
                                <x-ui.button type="submit" variant="primary" class="rounded-full">{{ __('production.orders.confirm') }}</x-ui.button>
                            </div>
                        </div>
                    </form>
                @empty
                    <div class="text-sm text-[var(--ui-text-muted)]">{{ __('production.orders.no_planned_components') }}</div>
                @endforelse
            </div>

            <form class="mt-6 space-y-4 border-t border-[var(--ui-border)] pt-5" method="POST" action="{{ route('production.orders.consumptions.store', $order) }}">
                @csrf
                <x-ui.input type="hidden" name="allow_unplanned" value="1" unstyled />
                <h3 class="text-sm font-semibold uppercase tracking-wide text-[var(--ui-text-muted)]">{{ __('production.orders.additional_consumption') }}</h3>
                <label class="block text-sm font-medium">{{ __('production.orders.unplanned_product') }}
                    <x-ui.select class="mt-2" name="product_id" required data-search="on" :data-placeholder="__('production.select_product')" data-ajax-url="{{ route('production.products.search', ['all' => 1]) }}" data-minimum-input-length="1">
                        <option value="">{{ __('production.select_product') }}</option>
                    </x-ui.select>
                </label>
                <div class="grid gap-4 sm:grid-cols-3">
                    <label class="block text-sm font-medium">{{ __('production.warehouse') }}
                        <x-ui.select class="mt-2" name="warehouse_id" required data-search="off">
                            <option value="">{{ __('production.select') }}</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </label>
                    <label class="block text-sm font-medium">{{ __('production.orders.quantity') }}
                        <x-ui.input class="mt-2" type="number" step="0.001" min="0.001" name="quantity_consumed" required />
                    </label>
                    <label class="block text-sm font-medium">{{ __('production.orders.lot') }}
                        <x-ui.input class="mt-2" name="lot_number" maxlength="120" />
                    </label>
                </div>
                <x-ui.button type="submit" variant="secondary" class="rounded-full">{{ __('production.orders.record_additional') }}</x-ui.button>
            </form>
        </x-ui.panel>
    </div>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">{{ __('production.orders.inspection_checkpoints') }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($order->outputs as $output)
                    <div class="rounded-xl border border-[var(--ui-border)] p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="text-sm text-[var(--ui-text-muted)]">{{ __('production.orders.posting_reference', ['id' => $output->id, 'operation' => $output->operation_no ?? '—']) }}</div>
                            <span class="rounded-full border border-[var(--ui-border)] px-2.5 py-1 text-xs">{{ __('production.status_labels.'.($output->inspection_status ?? 'PENDING')) }}</span>
                        </div>
                        <div class="mt-2 text-sm">{{ __('production.orders.good_and_scrap', ['good' => number_format((float) $output->quantity_completed, 3, ',', '.'), 'scrap' => number_format((float) $output->quantity_scrapped, 3, ',', '.')]) }}</div>

                        <form method="POST" action="{{ route('production.orders.outputs.inspection.update', [$order, $output]) }}" class="mt-3 grid gap-3 sm:grid-cols-3">
                            @csrf
                            <x-ui.select name="inspection_status" data-search="off">
                                @foreach (['APPROVED', 'PENDING', 'REJECTED'] as $inspectionStatus)
                                    <option value="{{ $inspectionStatus }}" @selected(($output->inspection_status ?? 'PENDING') === $inspectionStatus)>{{ __('production.status_labels.'.$inspectionStatus) }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.input name="inspection_notes" :value="$output->inspection_notes" :placeholder="__('production.orders.inspection_notes')" />
                            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('production.orders.update_checkpoint') }}</x-ui.button>
                        </form>
                    </div>
                @empty
                    <div class="text-sm text-[var(--ui-text-muted)]">{{ __('production.orders.no_postings') }}</div>
                @endforelse
            </div>
        </x-ui.panel>

        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">{{ __('production.orders.latest_consumptions') }}</h2>
            @if ($additionalConsumptions->isNotEmpty())
                <div class="mt-3 rounded-xl border border-[var(--ui-warning)] bg-[var(--ui-warning-soft)] p-3 text-sm">
                    <div class="font-semibold text-[var(--ui-warning-text)]">{{ __('production.orders.additional_consumptions') }}</div>
                    @foreach ($additionalConsumptions as $additional)
                        <div class="mt-1 text-[var(--ui-text-muted)]">{{ $additional['product']?->sku }} - {{ $additional['product']?->description }}: {{ number_format($additional['consumed_quantity'], 3, ',', '.') }}</div>
                    @endforeach
                </div>
            @endif
            <div class="mt-4 space-y-2 text-sm">
                @forelse ($order->materialConsumptions as $consumption)
                    <div class="rounded-xl border border-[var(--ui-border)] p-3">
                        <div><strong>{{ $consumption->product?->sku }}</strong> - {{ $consumption->product?->description }}</div>
                        <div class="text-[var(--ui-text-muted)]">{{ __('production.orders.consumption_summary', ['warehouse' => $consumption->warehouse?->code, 'consumption' => number_format((float) $consumption->quantity_consumed, 3, ',', '.'), 'scrap' => number_format((float) $consumption->quantity_scrapped, 3, ',', '.')]) }} @if (data_get($consumption->metadata, 'is_unplanned')) · <span class="font-semibold text-[var(--ui-warning-text)]">{{ __('production.orders.additional') }}</span> @endif</div>
                    </div>
                @empty
                    <div class="text-[var(--ui-text-muted)]">{{ __('production.orders.no_consumptions') }}</div>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</div>
@endsection
