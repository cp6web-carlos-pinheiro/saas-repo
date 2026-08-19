@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.module_routing'))
@section('client-page-title', __('ui.module_routing'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('production.routing.title', ['id' => $version->id]) }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('production.routing.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            @if ($version->status === 'DRAFT')
                <x-ui.button :href="route('production.routing.edit', $version)" variant="primary" class="rounded-full">{{ __('production.edit') }}</x-ui.button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('production.product')">{{ $version->product?->sku }} - {{ $version->product?->description }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.version')">{{ $version->version_number }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.status')">{{ __('production.status_labels.'.$version->status) }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('production.effective_from')" :value="$version->effective_from" />
            <x-ui.definition-item-date :label="__('production.effective_to')" :value="$version->effective_to" />
            <x-ui.definition-item :label="__('production.description')">{{ $version->description ?: '—' }}</x-ui.definition-item>
        </x-ui.definition-grid>

        @if ($version->status === 'DRAFT')
            <form method="POST" action="{{ route('production.routing.approve', $version) }}" class="mt-6 grid gap-3 sm:grid-cols-3">
                @csrf
                <label class="block text-sm font-medium">Vigencia inicial
                    <x-ui.input class="mt-2" type="date" name="effective_from" :value="old('effective_from', optional($version->effective_from)->format('Y-m-d'))" required />
                </label>
                <label class="block text-sm font-medium">Vigencia final
                    <x-ui.input class="mt-2" type="date" name="effective_to" :value="old('effective_to', optional($version->effective_to)->format('Y-m-d'))" />
                </label>
                <div class="flex items-end">
                    <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ __('production.routing.approve') }}</x-ui.button>
                </div>
            </form>
        @endif
    </x-ui.panel>

    @if ($version->status === 'DRAFT')
        <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">{{ __('production.routing.add_operation') }}</h2>
            <form method="POST" action="{{ route('production.routing.operations.store', $version) }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @csrf
                <label class="block text-sm font-medium">{{ __('production.work_center') }}
                    <x-ui.select class="mt-2" name="work_center_id" required data-search="on">
                        <option value="">{{ __('production.select') }}</option>
                        @foreach ($workCenters as $center)
                            <option value="{{ $center->id }}">{{ $center->code }} - {{ $center->name }}</option>
                        @endforeach
                    </x-ui.select>
                </label>
                <label class="block text-sm font-medium">{{ __('production.routing.operation_number') }}
                    <x-ui.input class="mt-2" name="operation_no" type="number" min="1" required />
                </label>
                <label class="block text-sm font-medium">{{ __('production.routing.operation_code') }}
                    <x-ui.input class="mt-2" name="operation_code" required />
                </label>
                <label class="block text-sm font-medium">{{ __('production.routing.operation_name') }}
                    <x-ui.input class="mt-2" name="operation_name" required />
                </label>
                <label class="block text-sm font-medium">{{ __('production.sequence') }}
                    <x-ui.input class="mt-2" name="sequence" type="number" min="1" required />
                </label>
                <label class="block text-sm font-medium">{{ __('production.orders.setup_time') }}
                    <x-ui.input class="mt-2" name="setup_time_minutes" type="text" inputmode="numeric" :value="old('setup_time_minutes', '00:00')" placeholder="00:00" data-duration-mask="true" />
                </label>
                <label class="block text-sm font-medium">{{ __('production.routing.runtime') }}
                    <x-ui.input class="mt-2" name="runtime_minutes" type="text" inputmode="numeric" :value="old('runtime_minutes', '00:00')" placeholder="00:00" data-duration-mask="true" />
                </label>
                <label class="block text-sm font-medium">{{ __('production.routing.queue') }}
                    <x-ui.input class="mt-2" name="queue_time_minutes" type="text" inputmode="numeric" :value="old('queue_time_minutes', '00:00')" placeholder="00:00" data-duration-mask="true" />
                </label>
                <label class="block text-sm font-medium">{{ __('production.routing.move') }}
                    <x-ui.input class="mt-2" name="move_time_minutes" type="text" inputmode="numeric" :value="old('move_time_minutes', '00:00')" placeholder="00:00" data-duration-mask="true" />
                </label>
                <div class="self-end pb-2">
                    <x-ui.checkbox name="is_outsourced" value="1">{{ __('production.routing.outsourced') }}</x-ui.checkbox>
                </div>
                <div class="lg:col-span-2 flex items-end">
                    <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ __('production.routing.add_operation') }}</x-ui.button>
                </div>
            </form>
        </x-ui.panel>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6">
        <h2 class="font-display text-xl font-bold">{{ __('production.routing.operations') }}</h2>
        <div class="mt-4 overflow-x-auto">
            <x-ui.table :caption="__('production.routing.operations')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <th class="px-3 py-2">{{ __('production.sequence') }}</th>
                        <th class="px-3 py-2">{{ __('production.operation') }}</th>
                        <th class="px-3 py-2">{{ __('production.code') }}</th>
                        <th class="px-3 py-2">{{ __('production.name') }}</th>
                        <th class="px-3 py-2">{{ __('production.work_center') }}</th>
                        <th class="px-3 py-2">{{ __('production.routing.total_time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($version->operations as $operation)
                        @php($totalMinutes = (float) $operation->setup_time_minutes + (float) $operation->runtime_minutes + (float) $operation->queue_time_minutes + (float) $operation->move_time_minutes)
                        <tr class="border-b border-[var(--ui-border)]">
                            <td class="px-3 py-2">{{ $operation->sequence }}</td>
                            <td class="px-3 py-2">{{ $operation->operation_no }}</td>
                            <td class="px-3 py-2">{{ $operation->operation_code }}</td>
                            <td class="px-3 py-2">{{ $operation->operation_name }}</td>
                            <td class="px-3 py-2">{{ $operation->workCenter?->code }} - {{ $operation->workCenter?->name }}</td>
                            <td class="px-3 py-2">{{ \App\Support\Duration::formatMinutes($totalMinutes) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-6 text-center text-[var(--ui-text-muted)]">{{ __('production.routing.empty_operations') }}</td></tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>
    </x-ui.panel>
</div>
@endsection
