@extends('layouts.client-area')

@section('title', __('ui.module_products').' | '.__('ui.product_versions'))
@section('client-page-title', __('product.versions_title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $editing ? __('product.version_edit') : __('product.version_create') }}</h1>
        </div>
        <x-ui.button :href="route('products.versions', ['product_id' => $product->id])" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <div class="rounded-2xl border border-[#dadce0] bg-white p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">{{ $product->sku }}</h2>
                    <p class="text-sm text-[#5f6368]">{{ $product->description ?? '—' }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                    {{ $product->is_active ? __('product.active') : __('product.inactive') }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ $editing ? route('products.versions.update', [$product, $version]) : route('products.versions.store', $product) }}" class="mt-6 grid gap-5 md:grid-cols-2">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div>
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="effective_from">{{ __('product.effective_from') }}</label>
                <x-ui.input id="effective_from" type="date" name="effective_from" :value="old('effective_from', $version?->effective_from?->format('Y-m-d'))" />
                @error('effective_from')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="effective_to">{{ __('product.effective_to') }}</label>
                <x-ui.input id="effective_to" type="date" name="effective_to" :value="old('effective_to', $version?->effective_to?->format('Y-m-d'))" />
                @error('effective_to')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="compatibility_rule">{{ __('product.compatibility_rule') }}</label>
                <x-ui.select id="compatibility_rule" name="compatibility_rule" required data-search="off">
                    @foreach (__('product.compatibility_rules') as $ruleValue => $ruleLabel)
                        <option value="{{ $ruleValue }}" @selected(old('compatibility_rule', $version?->compatibility_rule ?? 'FULL') === $ruleValue)>{{ $ruleLabel }}</option>
                    @endforeach
                </x-ui.select>
                @error('compatibility_rule')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="change_summary">{{ __('product.change_summary') }}</label>
                <x-ui.textarea id="change_summary" name="change_summary" rows="4">{{ old('change_summary', $version?->change_summary ?? '') }}</x-ui.textarea>
                @error('change_summary')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="payload_json">{{ __('product.payload') }}</label>
                <x-ui.textarea id="payload_json" name="payload_json" rows="12" class="font-mono text-sm">{{ old('payload_json', $payloadJson) }}</x-ui.textarea>
                <p class="mt-2 text-sm text-[#5f6368]">{{ __('product.payload_hint') }}</p>
                @error('payload_json')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-wrap gap-3 md:col-span-2">
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full">{{ __('ui.save') }}</x-ui.button>
                <x-ui.button :href="route('products.versions', ['product_id' => $product->id])" variant="surface-muted" class="rounded-full">{{ __('ui.cancel') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection