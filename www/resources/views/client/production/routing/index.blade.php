@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.module_routing'))
@section('client-page-title', __('ui.module_routing'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('ui.module_routing') }}</h1>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
        <h2 class="font-display text-xl font-bold">Nova versao de roteamento</h2>
        <form method="POST" action="{{ route('production.routing.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @csrf
            <label class="block text-sm font-medium lg:col-span-2">Produto
                <x-ui.select class="mt-2" name="product_id" required data-search="on">
                    <option value="">Selecione</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->description }}</option>
                    @endforeach
                </x-ui.select>
            </label>
            <label class="block text-sm font-medium">Versao
                <x-ui.input class="mt-2" name="version_number" type="number" min="1" required />
            </label>
            <label class="block text-sm font-medium">Vigencia inicial
                <x-ui.input class="mt-2" name="effective_from" type="date" />
            </label>
            <label class="block text-sm font-medium">Vigencia final
                <x-ui.input class="mt-2" name="effective_to" type="date" />
            </label>
            <label class="block text-sm font-medium lg:col-span-4">Descricao
                <x-ui.input class="mt-2" name="description" maxlength="255" />
            </label>
            <div class="flex items-end">
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">Criar versao</x-ui.button>
            </div>
        </form>
    </x-ui.panel>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
        <h2 class="font-display text-xl font-bold">Versoes cadastradas</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Produto</th>
                        <th class="px-3 py-2">Versao</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Operacoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($versions as $version)
                        <tr class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]" onclick="window.location='{{ route('production.routing.show', $version) }}'">
                            <td class="px-3 py-2">{{ $version->id }}</td>
                            <td class="px-3 py-2">{{ $version->product?->sku }} - {{ $version->product?->description }}</td>
                            <td class="px-3 py-2">{{ $version->version_number }}</td>
                            <td class="px-3 py-2">{{ $version->status }}</td>
                            <td class="px-3 py-2">{{ $version->operations_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-[#5f6368]">Nenhuma versao encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $versions->links() }}</div>
    </x-ui.panel>
</div>
@endsection
