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
                    <label class="block text-sm font-medium">Setup (min)
                        <x-ui.input class="mt-2" type="number" step="0.01" min="0" name="setup_time_minutes" value="0" />
                    </label>
                    <label class="block text-sm font-medium">Processo (min)
                        <x-ui.input class="mt-2" type="number" step="0.01" min="0" name="process_time_minutes" value="0" />
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
            <p class="mt-1 text-sm text-[#5f6368]">Baixa de matéria-prima e registro de perdas no consumo.</p>

            <form class="mt-4 space-y-4" method="POST" action="{{ route('production.orders.consumptions.store', $order) }}">
                @csrf
                <label class="block text-sm font-medium">Produto consumido
                    <x-ui.select class="mt-2" name="product_id" required data-search="on">
                        <option value="">Selecione um produto</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->description }}</option>
                        @endforeach
                    </x-ui.select>
                </label>

                <label class="block text-sm font-medium">Armazem de consumo
                    <x-ui.select class="mt-2" name="warehouse_id" required data-search="on">
                        <option value="">Selecione um armazem</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                        @endforeach
                    </x-ui.select>
                </label>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium">Qtd consumida
                        <x-ui.input class="mt-2" type="number" step="0.001" min="0.001" name="quantity_consumed" required />
                    </label>
                    <label class="block text-sm font-medium">Qtd refugada no consumo
                        <x-ui.input class="mt-2" type="number" step="0.001" min="0" name="quantity_scrapped" value="0" />
                    </label>
                </div>

                <label class="block text-sm font-medium">Lote consumido
                    <x-ui.input class="mt-2" name="lot_number" maxlength="120" />
                </label>

                <label class="block text-sm font-medium">Observações
                    <x-ui.textarea class="mt-2" name="notes" rows="3"></x-ui.textarea>
                </label>

                <x-ui.button type="submit" variant="brand-primary" class="rounded-full">Registrar consumo</x-ui.button>
            </form>
        </x-ui.panel>
    </div>

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
            <div class="mt-4 space-y-2 text-sm">
                @forelse ($order->materialConsumptions as $consumption)
                    <div class="rounded-xl border border-[#dadce0] p-3">
                        <div><strong>{{ $consumption->product?->sku }}</strong> - {{ $consumption->product?->description }}</div>
                        <div class="text-[#5f6368]">Armazem: {{ $consumption->warehouse?->code }} · Consumo: {{ number_format((float) $consumption->quantity_consumed, 3, ',', '.') }} · Refugo: {{ number_format((float) $consumption->quantity_scrapped, 3, ',', '.') }}</div>
                    </div>
                @empty
                    <div class="text-[#5f6368]">Nenhum consumo registrado.</div>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</div>
@endsection
