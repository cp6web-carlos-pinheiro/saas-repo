@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.work_centers'))
@section('client-page-title', __('ui.work_centers'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <h1 class="font-display text-3xl font-bold">{{ __('ui.work_centers') }}</h1>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
        <h2 class="font-display text-xl font-bold">Novo centro de trabalho</h2>
        <form method="POST" action="{{ route('production.work-centers.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <label class="block text-sm font-medium">Planta
                <x-ui.select class="mt-2" name="plant_id" required data-search="on">
                    <option value="">Selecione</option>
                    @foreach ($plants as $plant)
                        <option value="{{ $plant->id }}">{{ $plant->code }} - {{ $plant->name }}</option>
                    @endforeach
                </x-ui.select>
            </label>
            <label class="block text-sm font-medium">Codigo
                <x-ui.input class="mt-2" name="code" required />
            </label>
            <label class="block text-sm font-medium">Nome
                <x-ui.input class="mt-2" name="name" required />
            </label>
            <label class="block text-sm font-medium">Tipo
                <x-ui.select class="mt-2" name="resource_type" required data-search="off">
                    <option value="MACHINE">MACHINE</option>
                    <option value="LINE">LINE</option>
                </x-ui.select>
            </label>
            <label class="block text-sm font-medium">Capacidade/dia
                <x-ui.input class="mt-2" name="capacity_per_day" type="number" step="0.01" min="0" required />
            </label>
            <label class="block text-sm font-medium">Eficiência (%)
                <x-ui.input class="mt-2" name="efficiency_factor" type="number" step="0.01" min="0" max="1000" required />
            </label>
            <label class="inline-flex items-center gap-2 self-end pb-2 text-sm">
                <input type="checkbox" name="is_active" value="1" checked /> Ativo
            </label>
            <div class="flex items-end">
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">Criar centro</x-ui.button>
            </div>
        </form>
    </x-ui.panel>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-2">Codigo</th>
                        <th class="px-3 py-2">Nome</th>
                        <th class="px-3 py-2">Planta</th>
                        <th class="px-3 py-2">Tipo</th>
                        <th class="px-3 py-2">Capacidade/dia</th>
                        <th class="px-3 py-2">Eficiência</th>
                        <th class="px-3 py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($centers as $center)
                        <tr class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]" onclick="window.location='{{ route('production.work-centers.show', $center) }}'">
                            <td class="px-3 py-2">{{ $center->code }}</td>
                            <td class="px-3 py-2">{{ $center->name }}</td>
                            <td class="px-3 py-2">{{ $center->plant?->code }} - {{ $center->plant?->name }}</td>
                            <td class="px-3 py-2">{{ $center->resource_type }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $center->capacity_per_day, 2, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $center->efficiency_factor, 2, ',', '.') }}%</td>
                            <td class="px-3 py-2">{{ $center->is_active ? 'Ativo' : 'Inativo' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-6 text-center text-[#5f6368]">Nenhum centro encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $centers->links() }}</div>
    </x-ui.panel>
</div>
@endsection
