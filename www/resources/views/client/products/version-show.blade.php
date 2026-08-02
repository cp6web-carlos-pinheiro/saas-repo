@extends('layouts.client-area')

@section('title', __('product.version_details').' | '.__('ui.app_name'))
@section('client-page-title', __('product.versions_title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('product.version_details') }}</h1>
            <p class="mt-1 text-sm text-[#5f6368]">{{ $product->sku }} - {{ $product->description ?? '—' }}</p>
        </div>
        <span class="rounded-full px-3 py-1 text-xs {{ $version->status === 'DRAFT' ? 'bg-slate-100 text-slate-600' : ($version->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">
            {{ __('product.version_status_'.$version->status) }}
        </span>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <dl class="divide-y divide-[#dadce0]">
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.version') }}</dt>
                <dd class="font-medium">{{ $version->version_number }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.status') }}</dt>
                <dd class="font-medium">{{ __('product.version_status_'.$version->status) }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.effective_from') }}</dt>
                <dd class="font-medium">{{ $version->effective_from?->format('d/m/Y') ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.effective_to') }}</dt>
                <dd class="font-medium">{{ $version->effective_to?->format('d/m/Y') ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.compatibility_rule') }}</dt>
                <dd class="font-medium">{{ __('product.compatibility_rules.'.$version->compatibility_rule) }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.change_summary') }}</dt>
                <dd class="font-medium text-right">{{ $version->change_summary ?? '—' }}</dd>
            </div>
            <div class="py-4">
                <dt class="mb-3 text-[#5f6368]">{{ __('product.payload') }}</dt>
                <dd class="overflow-x-auto rounded-2xl bg-slate-50 p-4 text-sm leading-6">
                    <pre class="whitespace-pre-wrap break-words">{{ json_encode($version->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                </dd>
            </div>
        </dl>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('products.versions', ['product_id' => $product->id])" variant="surface-muted" class="rounded-full">{{ __('ui.back') }}</x-ui.button>

            @if ($version->status === 'DRAFT')
                <x-ui.button :href="route('products.versions.edit', [$product, $version])" variant="brand-primary" class="rounded-full">{{ __('product.edit') }}</x-ui.button>

                <form method="POST" action="{{ route('products.versions.approve', [$product, $version]) }}">
                    @csrf
                    <x-ui.button type="submit" variant="surface-muted" class="rounded-full">{{ __('product.approve') }}</x-ui.button>
                </form>
            @endif

            @if ($version->status === 'APPROVED')
                <form method="POST" action="{{ route('products.versions.obsolete', [$product, $version]) }}">
                    @csrf
                    <x-ui.button type="submit" variant="surface-muted" class="rounded-full">{{ __('product.obsolete_action') }}</x-ui.button>
                </form>
            @endif

            @if ($version->status !== 'APPROVED')
                <form method="POST" action="{{ route('products.versions.destroy', [$product, $version]) }}" data-admin-delete-confirm data-admin-name="{{ $product->sku }} v{{ $version->version_number }}" data-confirm-title="{{ __('product.confirm_delete_title') }}" data-confirm-text="{{ __('product.confirm_delete_text') }}" data-confirm-confirm="{{ __('product.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('product.confirm_delete_cancel') }}">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger-outline" class="rounded-full">{{ __('product.version_remove') }}</x-ui.button>
                </form>
            @endif
        </div>
    </x-ui.panel>
</div>
@endsection