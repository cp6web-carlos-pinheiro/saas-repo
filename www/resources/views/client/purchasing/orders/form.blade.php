@extends('layouts.client-area')

@php($editing = $order !== null)

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_order'))
@section('client-page-title', $editing ? __('purchase_order.edit') : __('purchase_order.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('purchase_order.edit') : __('purchase_order.create') }}</h1>
        <x-ui.button :href="$editing ? route('purchasing.orders.show', $order) : route('purchasing.orders.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('purchasing.orders.update', $order) : route('purchasing.orders.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('purchase_order.supplier') }}
                    <x-ui.select name="supplier_id" class="mt-2" required>
                        <option value="">{{ __('purchase_order.select_supplier') }}</option>
                        @foreach ($suppliers as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('supplier_id', $order?->supplier_id) === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('supplier_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_order.requisition') }}
                    <x-ui.select name="purchase_requisition_id" class="mt-2">
                        <option value="">{{ __('purchase_order.select_requisition') }}</option>
                        @foreach ($requisitions as $id => $number)
                            <option value="{{ $id }}" @selected((string) old('purchase_requisition_id', $order?->purchase_requisition_id) === (string) $id)>{{ $number }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('purchase_requisition_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <label class="block text-sm font-medium">
                    {{ __('purchase_order.order_date') }}
                    <x-ui.input type="date" name="order_date" :value="old('order_date', $order?->order_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" class="mt-2" required />
                    @error('order_date')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_order.expected_delivery_date') }}
                    <x-ui.input type="date" name="expected_delivery_date" :value="old('expected_delivery_date', $order?->expected_delivery_date?->format('Y-m-d'))" class="mt-2" />
                    @error('expected_delivery_date')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_order.status') }}
                    <x-ui.select name="status" class="mt-2" required data-search="off">
                        <option value="DRAFT" @selected(old('status', $order?->status ?? 'DRAFT') === 'DRAFT')>{{ __('purchase_order.status_draft') }}</option>
                        <option value="APPROVED" @selected(old('status', $order?->status ?? 'DRAFT') === 'APPROVED')>{{ __('purchase_order.status_approved') }}</option>
                        <option value="CANCELLED" @selected(old('status', $order?->status ?? 'DRAFT') === 'CANCELLED')>{{ __('purchase_order.status_cancelled') }}</option>
                    </x-ui.select>
                    @error('status')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('purchase_order.notes') }}
                <x-ui.textarea name="notes" class="mt-2" rows="4">{{ old('notes', $order?->notes) }}</x-ui.textarea>
                @error('notes')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('purchasing.orders.show', $order) : route('purchasing.orders.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('purchase_order.save') : __('purchase_order.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
