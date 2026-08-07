@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.$order->order_number)
@section('client-page-title', $order->order_number)

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $order->order_number }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('production.analytics.index')" variant="surface-muted" class="rounded-full">Indicadores</x-ui.button>
            <x-ui.button :href="route('production.orders.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('sale.status')">{{ $order->status }}</x-ui.definition-item>
            <x-ui.definition-item label="Venda">{{ $order->sales_order_reference ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('sale.product')">{{ $order->product?->sku }} - {{ $order->product?->description }}</x-ui.definition-item>
            <x-ui.definition-item label="Armazem">{{ $order->warehouse?->code }} - {{ $order->warehouse?->name }}</x-ui.definition-item>
            <x-ui.definition-item label="Qtd planejada">{{ number_format((float) $order->quantity_planned, 3, ',', '.') }}</x-ui.definition-item>
            <x-ui.definition-item label="Qtd produzida">{{ number_format((float) $order->quantity_produced, 3, ',', '.') }}</x-ui.definition-item>
            <x-ui.definition-item label="Qtd refugada">{{ number_format((float) $order->quantity_scrapped, 3, ',', '.') }}</x-ui.definition-item>
            <x-ui.definition-item-date label="Inicio previsto" :value="$order->scheduled_start_date" />
            <x-ui.definition-item-date label="Fim previsto" :value="$order->scheduled_end_date" />
        </x-ui.definition-grid>

        <div class="mt-6 flex flex-wrap gap-3">
            @if ($order->status === 'DRAFT')
                <form method="POST" action="{{ route('production.orders.release', $order) }}">
                    @csrf
                    <x-ui.button type="submit" variant="brand-primary" class="rounded-full">Liberar ordem</x-ui.button>
                </form>
            @endif

            @if (! in_array($order->status, ['COMPLETED', 'CANCELLED'], true))
                <form method="POST" action="{{ route('production.orders.complete', $order) }}">
                    @csrf
                    <x-ui.button type="submit" variant="surface-muted" class="rounded-full">Concluir ordem</x-ui.button>
                </form>
            @endif
        </div>
    </x-ui.panel>

    @if ($order->status !== 'COMPLETED')
    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">Registrar apontamento</h2>
            <p class="mt-1 text-sm text-[#5f6368]">Use este formulário para produção parcial e refugo por operação.</p>

            <form class="mt-4 space-y-4" method="POST" action="{{ route('production.orders.outputs.store', $order) }}">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium">Qtd produzida
                        <x-ui.input class="mt-2" type="number" step="0.001" min="0" name="quantity_completed" value="0" required />
                    </label>
                    <label class="block text-sm font-medium">Qtd refugada
                        <x-ui.input class="mt-2" type="number" step="0.001" min="0" name="quantity_scrapped" value="0" />
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium">Operação
                        <x-ui.input class="mt-2" type="number" min="1" name="operation_no" />
                    </label>
                    <label class="block text-sm font-medium">Lote do acabado
                        <x-ui.input class="mt-2" name="lot_number" maxlength="120" />
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium">Setup (HH:MM)
                        <x-ui.input class="mt-2" type="text" inputmode="numeric" name="setup_time_minutes" :value="old('setup_time_minutes', '00:00')" placeholder="00:00" data-duration-mask="true" />
                    </label>
                    <label class="block text-sm font-medium">Processo (HH:MM)
                        <x-ui.input class="mt-2" type="text" inputmode="numeric" name="process_time_minutes" :value="old('process_time_minutes', '00:00')" placeholder="00:00" data-duration-mask="true" />
                    </label>
                </div>

                <label class="block text-sm font-medium">Status de inspeção
                    <x-ui.select class="mt-2" name="inspection_status" data-search="off">
                        <option value="APPROVED">APPROVED</option>
                        <option value="PENDING">PENDING</option>
                        <option value="REJECTED">REJECTED</option>
                    </x-ui.select>
                </label>

                <x-ui.button type="submit" variant="brand-primary" class="rounded-full">Registrar apontamento</x-ui.button>
            </form>
        </x-ui.panel>

        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">Registrar consumo</h2>
            <p class="mt-1 text-sm text-[#5f6368]">Ajuste a quantidade prevista ou registre explicitamente um consumo adicional.</p>

            <div class="mt-4 space-y-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-[#5f6368]">Produtos previstos na BOM</h3>
                @forelse ($plannedMaterials as $plannedMaterial)
                    @php($component = $plannedMaterial['component'])
                    <form class="rounded-xl border border-[#dadce0] p-3" method="POST" action="{{ route('production.orders.consumptions.store', $order) }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $component->component_product_id }}">
                        <input type="hidden" name="reference_bom_component_id" value="{{ $component->id }}">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="font-medium">{{ $component->componentProduct->sku }} - {{ $component->componentProduct->description }}</div>
                                <div class="text-xs text-[#5f6368]">Previsto: {{ number_format($plannedMaterial['planned_quantity'], 3, ',', '.') }} · Consumido: {{ number_format($plannedMaterial['consumed_quantity'], 3, ',', '.') }} · Saldo: {{ number_format($plannedMaterial['remaining_quantity'], 3, ',', '.') }}</div>
                            </div>
                            <div class="flex items-end gap-2">
                                <label class="text-xs text-[#5f6368]">Confirmar quantidade
                                    <x-ui.input class="mt-1 w-32" type="number" step="0.001" min="0.001" name="quantity_consumed" value="{{ $plannedMaterial['remaining_quantity'] > 0 ? $plannedMaterial['remaining_quantity'] : '' }}" required />
                                </label>
                                <label class="text-xs text-[#5f6368]">Armazém
                                    <x-ui.select class="mt-1 w-40" name="warehouse_id" required data-search="off">
                                        <option value="">Selecione</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->code }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </label>
                                <x-ui.button type="submit" variant="brand-primary" class="rounded-full">Confirmar</x-ui.button>
                            </div>
                        </div>
                    </form>
                @empty
                    <div class="text-sm text-[#5f6368]">Nenhum componente previsto foi encontrado na BOM congelada.</div>
                @endforelse
            </div>

            <form class="mt-6 space-y-4 border-t border-[#dadce0] pt-5" method="POST" action="{{ route('production.orders.consumptions.store', $order) }}">
                @csrf
                <input type="hidden" name="allow_unplanned" value="1">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-[#5f6368]">Adicionar consumo adicional</h3>
                <label class="block text-sm font-medium">Produto não previsto
                    <x-ui.select class="mt-2" name="product_id" required data-search="on" data-placeholder="Selecione um produto" data-ajax-url="{{ route('production.products.search', ['all' => 1]) }}" data-minimum-input-length="1">
                        <option value="">Selecione um produto</option>
                    </x-ui.select>
                </label>
                <div class="grid gap-4 sm:grid-cols-3">
                    <label class="block text-sm font-medium">Armazém
                        <x-ui.select class="mt-2" name="warehouse_id" required data-search="off">
                            <option value="">Selecione</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </label>
                    <label class="block text-sm font-medium">Quantidade
                        <x-ui.input class="mt-2" type="number" step="0.001" min="0.001" name="quantity_consumed" required />
                    </label>
                    <label class="block text-sm font-medium">Lote
                        <x-ui.input class="mt-2" name="lot_number" maxlength="120" />
                    </label>
                </div>
                <x-ui.button type="submit" variant="surface-muted" class="rounded-full">Registrar adicional</x-ui.button>
            </form>
        </x-ui.panel>
    </div>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">Checkpoints de inspeção</h2>
            <div class="mt-4 space-y-3">
                @forelse ($order->outputs as $output)
                    <div class="rounded-xl border border-[#dadce0] p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="text-sm text-[#5f6368]">Apontamento #{{ $output->id }} · Operação {{ $output->operation_no ?? '—' }}</div>
                            <span class="rounded-full border border-[#dadce0] px-2.5 py-1 text-xs">{{ $output->inspection_status ?? 'PENDING' }}</span>
                        </div>
                        <div class="mt-2 text-sm">Boa: {{ number_format((float) $output->quantity_completed, 3, ',', '.') }} · Refugo: {{ number_format((float) $output->quantity_scrapped, 3, ',', '.') }}</div>

                        <form method="POST" action="{{ route('production.orders.outputs.inspection.update', [$order, $output]) }}" class="mt-3 grid gap-3 sm:grid-cols-3">
                            @csrf
                            <x-ui.select name="inspection_status" data-search="off">
                                <option value="APPROVED" @selected(($output->inspection_status ?? 'PENDING') === 'APPROVED')>APPROVED</option>
                                <option value="PENDING" @selected(($output->inspection_status ?? 'PENDING') === 'PENDING')>PENDING</option>
                                <option value="REJECTED" @selected(($output->inspection_status ?? 'PENDING') === 'REJECTED')>REJECTED</option>
                            </x-ui.select>
                            <x-ui.input name="inspection_notes" :value="$output->inspection_notes" placeholder="Notas da inspeção" />
                            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">Atualizar checkpoint</x-ui.button>
                        </form>
                    </div>
                @empty
                    <div class="text-sm text-[#5f6368]">Nenhum apontamento registrado ainda.</div>
                @endforelse
            </div>
        </x-ui.panel>

        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">Ultimos consumos</h2>
            @if ($additionalConsumptions->isNotEmpty())
                <div class="mt-3 rounded-xl border border-[#fbbc04] bg-[#fff8e1] p-3 text-sm">
                    <div class="font-semibold text-[#8a5a00]">Consumos adicionais desta OP</div>
                    @foreach ($additionalConsumptions as $additional)
                        <div class="mt-1 text-[#5f6368]">{{ $additional['product']?->sku }} - {{ $additional['product']?->description }}: {{ number_format($additional['consumed_quantity'], 3, ',', '.') }}</div>
                    @endforeach
                </div>
            @endif
            <div class="mt-4 space-y-2 text-sm">
                @forelse ($order->materialConsumptions as $consumption)
                    <div class="rounded-xl border border-[#dadce0] p-3">
                        <div><strong>{{ $consumption->product?->sku }}</strong> - {{ $consumption->product?->description }}</div>
                        <div class="text-[#5f6368]">Armazem: {{ $consumption->warehouse?->code }} · Consumo: {{ number_format((float) $consumption->quantity_consumed, 3, ',', '.') }} · Refugo: {{ number_format((float) $consumption->quantity_scrapped, 3, ',', '.') }} @if (data_get($consumption->metadata, 'is_unplanned')) · <span class="font-semibold text-[#8a5a00]">Adicional</span> @endif</div>
                    </div>
                @empty
                    <div class="text-[#5f6368]">Nenhum consumo registrado.</div>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</div>
@endsection
