@extends('layouts.client-area')

@section('title', $supplier->name.' | '.__('supplier.title'))
@section('client-page-title', __('supplier.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $supplier->name }}</h1>
        </div>
        <span class="rounded-full px-3 py-1 text-xs {{ $supplier->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
            {{ $supplier->status === 'ACTIVE' ? __('supplier.active') : __('supplier.inactive') }}
        </span>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <dl class="divide-y divide-[#dadce0]">
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('supplier.code') }}</dt>
                <dd class="font-medium">{{ $supplier->code }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('supplier.email') }}</dt>
                <dd class="font-medium">{{ $supplier->email ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('supplier.phone') }}</dt>
                <dd class="font-medium">{{ $supplier->phone ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('supplier.default_lead_time_days') }}</dt>
                <dd class="font-medium">{{ $supplier->default_lead_time_days }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('supplier.payment_terms') }}</dt>
                <dd class="font-medium">{{ $supplier->payment_terms ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('supplier.created_at') }}</dt>
                <dd class="font-medium">{{ $supplier->created_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('purchasing.suppliers.index')" variant="surface-muted" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('purchasing.suppliers.edit', $supplier)" variant="brand-primary" class="rounded-full">{{ __('supplier.edit') }}</x-ui.button>

            <form method="POST" action="{{ route('purchasing.suppliers.destroy', $supplier) }}" data-admin-delete-confirm data-admin-name="{{ $supplier->name }}" data-confirm-title="{{ __('supplier.confirm_delete_title') }}" data-confirm-text="{{ __('supplier.confirm_delete_text') }}" data-confirm-confirm="{{ __('supplier.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('supplier.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger-outline" class="rounded-full">{{ __('supplier.remove') }}</x-ui.button>
            </form>
        </div>
    </x-ui.panel>
</div>
@endsection
