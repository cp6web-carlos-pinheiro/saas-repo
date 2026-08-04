@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.module_routing'))
@section('client-page-title', __('ui.module_routing'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('ui.module_routing') }}</h1>
        <x-ui.button :href="route('production.routing.create')" variant="brand-primary" class="rounded-full">Nova versão</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <x-ui.input name="search" :value="$search" class="min-w-0 flex-1" placeholder="Buscar por versão, SKU, produto ou descrição" />
            <input type="hidden" name="status" value="{{ $status }}">
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">Filtrar</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('production.routing.index', array_merge(['search' => $search, 'status' => $status], $overrides)))
        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>Todos</a>
            <a href="{{ $filterUrl(['status' => 'DRAFT']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'DRAFT' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>DRAFT</a>
            <a href="{{ $filterUrl(['status' => 'APPROVED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'APPROVED' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>APPROVED</a>
            <a href="{{ $filterUrl(['status' => 'OBSOLETE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'OBSOLETE' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>OBSOLETE</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Produto</th>
                        <th class="px-3 py-2">Versão</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Operações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($versions as $version)
                        <tr class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]" tabindex="0"
                            onclick="window.location='{{ route('production.routing.show', $version) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('production.routing.show', $version) }}'; }"
                        >
                            <td class="px-3 py-2">{{ $version->id }}</td>
                            <td class="px-3 py-2">{{ $version->product?->sku }} - {{ $version->product?->description }}</td>
                            <td class="px-3 py-2">{{ $version->version_number }}</td>
                            <td class="px-3 py-2">{{ $version->status }}</td>
                            <td class="px-3 py-2">{{ $version->operations_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-[#5f6368]">Nenhuma versão encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $versions->links() }}</div>
    </x-ui.panel>
</div>
@endsection
