@extends('layouts.client-area')

@section('title', __('ui.module_products').' | '.__('ui.product_versions'))
@section('client-page-title', __('product.versions_title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $editing ? __('product.version_edit') : __('product.version_create') }}</h1>
        </div>
        <x-ui.button :href="route('products.versions', ['product_id' => $product->id])" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">{{ $product->sku }}</h2>
                    <p class="text-sm text-[var(--ui-text-muted)]">{{ $product->description ?? '—' }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs {{ $product->is_active ? 'bg-[var(--ui-success-soft)] text-[var(--ui-success)]' : 'bg-[var(--ui-surface-muted)] text-[var(--ui-text-muted)]' }}">
                    {{ $product->is_active ? __('product.active') : __('product.inactive') }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ $editing ? route('products.versions.update', [$product, $version]) : route('products.versions.store', $product) }}" class="mt-6 grid gap-5 md:grid-cols-2">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-ui.field label="{{ __('product.effective_from') }}" for="effective_from" :error="$errors->first('effective_from')">
                <x-ui.input id="effective_from" type="date" name="effective_from" :value="old('effective_from', $version?->effective_from?->format('Y-m-d'))"  :aria-describedby="$errors->has('effective_from') ? 'effective_from-error' : null"/>
            </x-ui.field>

            <x-ui.field label="{{ __('product.effective_to') }}" for="effective_to" :error="$errors->first('effective_to')">
                <x-ui.input id="effective_to" type="date" name="effective_to" :value="old('effective_to', $version?->effective_to?->format('Y-m-d'))"  :aria-describedby="$errors->has('effective_to') ? 'effective_to-error' : null"/>
            </x-ui.field>

            <x-ui.field label="{{ __('product.compatibility_rule') }}" for="compatibility_rule" :required="true" :error="$errors->first('compatibility_rule')">
                <x-ui.select id="compatibility_rule" name="compatibility_rule" required data-search="off" :aria-describedby="$errors->has('compatibility_rule') ? 'compatibility_rule-error' : null">
                    @foreach (__('product.compatibility_rules') as $ruleValue => $ruleLabel)
                        <option value="{{ $ruleValue }}" @selected(old('compatibility_rule', $version?->compatibility_rule ?? 'FULL') === $ruleValue)>{{ $ruleLabel }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field class="md:col-span-2" label="{{ __('product.change_summary') }}" for="change_summary" :error="$errors->first('change_summary')">
                <x-ui.textarea id="change_summary" name="change_summary" rows="4" :aria-describedby="$errors->has('change_summary') ? 'change_summary-error' : null">{{ old('change_summary', $version?->change_summary ?? '') }}</x-ui.textarea>
            </x-ui.field>

            <x-ui.field class="md:col-span-2" label="{{ __('product.payload') }}" for="payload_json" hint="{{ __('product.payload_hint') }}" :error="$errors->first('payload_json')">
                <x-ui.textarea id="payload_json" name="payload_json" rows="12" class="font-mono text-sm" :aria-describedby="$errors->has('payload_json') ? 'payload_json-error' : 'payload_json-hint'">{{ old('payload_json', $payloadJson) }}</x-ui.textarea>
            </x-ui.field>

            <div class="flex flex-wrap gap-3 md:col-span-2">
                <x-ui.button type="submit" variant="primary" class="rounded-full">{{ __('ui.save') }}</x-ui.button>
                <x-ui.button :href="route('products.versions', ['product_id' => $product->id])" variant="secondary" class="rounded-full">{{ __('ui.cancel') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
