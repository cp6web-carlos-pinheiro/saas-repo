@extends('layouts.client-area')

@php($editing = $receipt !== null)

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_receipt'))
@section('client-page-title', $editing ? __('purchase_receipt.edit') : __('purchase_receipt.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('purchase_receipt.edit') : __('purchase_receipt.create') }}</h1>
        <x-ui.button :href="$editing ? route('purchasing.receipts.show', $receipt) : route('purchasing.receipts.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('purchasing.receipts.update', $receipt) : route('purchasing.receipts.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('purchase_receipt.supplier') }}
                    <x-ui.select name="supplier_id" class="mt-2">
                        <option value="">{{ __('purchase_receipt.select_supplier') }}</option>
                        @foreach ($suppliers as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('supplier_id', $receipt?->supplier_id) === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('supplier_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_receipt.order') }}
                    <x-ui.select name="purchase_order_id" class="mt-2">
                        <option value="">{{ __('purchase_receipt.select_order') }}</option>
                        @foreach ($orders as $id => $number)
                            <option value="{{ $id }}" @selected((string) old('purchase_order_id', $receipt?->purchase_order_id) === (string) $id)>{{ $number }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('purchase_order_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('purchase_receipt.receipt_date') }}
                    <x-ui.input type="date" name="receipt_date" :value="old('receipt_date', $receipt?->receipt_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" class="mt-2" required />
                    @error('receipt_date')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_receipt.status') }}
                    <x-ui.select name="status" class="mt-2" required data-search="off">
                        <option value="DRAFT" @selected(old('status', $receipt?->status ?? 'DRAFT') === 'DRAFT')>{{ __('purchase_receipt.status_draft') }}</option>
                        <option value="POSTED" @selected(old('status', $receipt?->status ?? 'DRAFT') === 'POSTED')>{{ __('purchase_receipt.status_posted') }}</option>
                        <option value="CANCELLED" @selected(old('status', $receipt?->status ?? 'DRAFT') === 'CANCELLED')>{{ __('purchase_receipt.status_cancelled') }}</option>
                    </x-ui.select>
                    @error('status')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('purchase_receipt.notes') }}
                <x-ui.textarea name="notes" class="mt-2" rows="4">{{ old('notes', $receipt?->notes) }}</x-ui.textarea>
                @error('notes')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('purchasing.receipts.show', $receipt) : route('purchasing.receipts.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('purchase_receipt.save') : __('purchase_receipt.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
