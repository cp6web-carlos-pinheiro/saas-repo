@php
    $treeTone = match ($node['status']) {
        'completed', 'available' => 'bg-[#e6f4ea] text-[#137333]',
        'in_progress' => 'bg-[#e8f0fe] text-[#174ea6]',
        'shortage', 'blocked' => 'bg-[#fce8e6] text-[#b3261e]',
        default => 'bg-[#fef7e0] text-[#8a5a00]',
    };
@endphp

<li class="relative {{ $node['level'] > 0 ? 'ml-5 border-l border-[#dadce0] pl-5' : '' }}">
    <details open class="group/tree">
        <summary class="cursor-pointer list-none rounded-xl border border-[#dadce0] bg-white p-3 transition hover:bg-[#f8fafd] [&::-webkit-details-marker]:hidden">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-2">
                    @if ($node['children'] !== [])
                        <span class="transition group-open/tree:rotate-90" aria-hidden="true">›</span>
                    @else
                        <span class="w-2" aria-hidden="true">•</span>
                    @endif
                    <div>
                        <div class="font-semibold">{{ $node['sku'] }} - {{ $node['description'] }}</div>
                        <div class="mt-1 text-xs text-[#5f6368]">
                            {{ __('sale.production_status.tree_quantities', [
                                'required' => $formatQuantity($node['required_quantity']),
                                'reserved' => $formatQuantity($node['reserved_quantity']),
                                'stock' => $formatQuantity($node['available_quantity']),
                                'unit' => $node['unit'],
                            ]) }}
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    @if ($node['order'])
                        <span>{{ $node['order']['order_number'] ?? __('sale.production_status.not_created') }}</span>
                    @endif
                    @if ($node['order']['scheduled_end'] ?? null)
                        <span>{{ \Illuminate\Support\Carbon::parse($node['order']['scheduled_end'])->format('d/m/Y') }}</span>
                    @endif
                    <span class="rounded-full px-2.5 py-1 font-semibold {{ $treeTone }}">{{ __('sale.production_status.tree_status_'.$node['status']) }}</span>
                </div>
            </div>
            <div class="mt-2 grid gap-2 text-xs text-[#5f6368] sm:grid-cols-4">
                <span>{{ __('sale.production_status.in_production') }}: <strong>{{ $formatQuantity($node['in_production']) }}</strong></span>
                <span>{{ __('sale.production_status.in_purchase') }}: <strong>{{ $formatQuantity($node['in_purchase']) }}</strong></span>
                <span>{{ __('sale.production_status.received') }}: <strong>{{ $formatQuantity($node['received_quantity']) }}</strong></span>
                <span>{{ __('sale.production_status.net_shortage') }}: <strong class="{{ $node['net_shortage'] > 0 ? 'text-[#b3261e]' : 'text-[#137333]' }}">{{ $formatQuantity($node['net_shortage']) }}</strong></span>
            </div>
        </summary>

        @if ($node['children'] !== [])
            <ul class="mt-2 space-y-2">
                @foreach ($node['children'] as $childNode)
                    @include('client.sales._production-tree-node', ['node' => $childNode])
                @endforeach
            </ul>
        @endif
    </details>
</li>
