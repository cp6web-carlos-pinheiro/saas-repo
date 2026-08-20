@extends('layouts.client-area')

@section('title', __('ui.module_products').' | '.__('ui.product_versions'))
@section('client-page-title', __('product.versions_title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('product.version_details') }}" subtitle="{{ $product->sku }} - {{ $product->description ?? '—' }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('products.versions', ['product_id' => $product->id])" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>

            @if ($version->status === 'DRAFT')
                <x-ui.button :href="route('products.versions.edit', [$product, $version])" variant="primary" class="rounded-full">{{ __('product.edit') }}</x-ui.button>
            @endif

            @if ($version->status !== 'APPROVED')
                <form method="POST" action="{{ route('products.versions.destroy', [$product, $version]) }}" data-admin-delete-confirm data-admin-name="{{ $product->sku }} v{{ $version->version_number }}" data-confirm-title="{{ __('product.confirm_delete_title') }}" data-confirm-text="{{ __('product.confirm_delete_text') }}" data-confirm-confirm="{{ __('product.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('product.confirm_delete_cancel') }}">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger" class="rounded-full">{{ __('product.version_remove') }}</x-ui.button>
                </form>
            @endif
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('product.version')">{{ $version->version_number }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('product.effective_from')">{{ $version->effective_from?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('product.effective_to')">{{ $version->effective_to?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item class="sm:col-span-2 xl:col-span-1" :label="__('product.compatibility_rule')">{{ __('product.compatibility_rules.'.$version->compatibility_rule) }}</x-ui.definition-item>
            <x-ui.definition-item-status :label="__('product.status')" :value="__('product.version_status_'.$version->status)" :tone="$version->status === 'APPROVED' ? 'success' : ($version->status === 'OBSOLETE' ? 'warning' : 'neutral')" />
            <x-ui.definition-item class="sm:col-span-2 xl:col-span-3" :label="__('product.change_summary')">{{ $version->change_summary ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item class="sm:col-span-2 xl:col-span-3" :label="__('product.payload')" valueClass="overflow-x-auto rounded-2xl bg-[var(--ui-surface-muted)] p-4 text-sm leading-6">
                <pre class="whitespace-pre-wrap break-words">{{ json_encode($version->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </x-ui.definition-item>
        </x-ui.definition-grid>

        @if ($version->status === 'DRAFT' || $version->status === 'APPROVED')
            <div class="mt-8 flex flex-wrap gap-3">
                @if ($version->status === 'DRAFT')
                    <form method="POST" action="{{ route('products.versions.approve', [$product, $version]) }}">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" class="rounded-full">{{ __('product.approve') }}</x-ui.button>
                    </form>
                @endif

                @if ($version->status === 'APPROVED')
                    <form method="POST" action="{{ route('products.versions.obsolete', [$product, $version]) }}">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" class="rounded-full">{{ __('product.obsolete_action') }}</x-ui.button>
                    </form>
                @endif
            </div>
        @endif
    </x-ui.panel>
</div>
@endsection
