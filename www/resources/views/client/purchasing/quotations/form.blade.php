@extends('layouts.client-area')

@php($editing = $quotation !== null)

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_quotation'))
@section('client-page-title', $editing ? __('purchase_quotation.edit') : __('purchase_quotation.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('purchase_quotation.edit') : __('purchase_quotation.create') }}</h1>
        <x-ui.button :href="$editing ? route('purchasing.quotations.show', $quotation) : route('purchasing.quotations.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('purchasing.quotations.update', $quotation) : route('purchasing.quotations.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('purchase_quotation.supplier') }}
                    <x-ui.select name="supplier_id" class="mt-2">
                        <option value="">{{ __('purchase_quotation.select_supplier') }}</option>
                        @foreach ($suppliers as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('supplier_id', $quotation?->supplier_id) === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('supplier_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_quotation.requisition') }}
                    <x-ui.select name="purchase_requisition_id" class="mt-2">
                        <option value="">{{ __('purchase_quotation.select_requisition') }}</option>
                        @foreach ($requisitions as $id => $number)
                            <option value="{{ $id }}" @selected((string) old('purchase_requisition_id', $quotation?->purchase_requisition_id) === (string) $id)>{{ $number }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('purchase_requisition_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-4">
                <label class="block text-sm font-medium">
                    {{ __('purchase_quotation.quotation_date') }}
                    <x-ui.input type="date" name="quotation_date" :value="old('quotation_date', $quotation?->quotation_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" class="mt-2" required />
                    @error('quotation_date')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_quotation.valid_until') }}
                    <x-ui.input type="date" name="valid_until" :value="old('valid_until', $quotation?->valid_until?->format('Y-m-d'))" class="mt-2" />
                    @error('valid_until')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_quotation.status') }}
                    <x-ui.select name="status" class="mt-2" required data-search="off">
                        <option value="DRAFT" @selected(old('status', $quotation?->status ?? 'DRAFT') === 'DRAFT')>{{ __('purchase_quotation.status_draft') }}</option>
                        <option value="RECEIVED" @selected(old('status', $quotation?->status ?? 'DRAFT') === 'RECEIVED')>{{ __('purchase_quotation.status_received') }}</option>
                        <option value="APPROVED" @selected(old('status', $quotation?->status ?? 'DRAFT') === 'APPROVED')>{{ __('purchase_quotation.status_approved') }}</option>
                        <option value="REJECTED" @selected(old('status', $quotation?->status ?? 'DRAFT') === 'REJECTED')>{{ __('purchase_quotation.status_rejected') }}</option>
                    </x-ui.select>
                    @error('status')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_quotation.amount') }}
                    <x-ui.input name="amount" :value="old('amount', $editing ? number_format(((int) $quotation->amount_cents) / 100, 2, ',', '.') : '0,00')" class="mt-2" required />
                    @error('amount')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('purchase_quotation.notes') }}
                <x-ui.textarea name="notes" class="mt-2" rows="4">{{ old('notes', $quotation?->notes) }}</x-ui.textarea>
                @error('notes')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('purchasing.quotations.show', $quotation) : route('purchasing.quotations.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('purchase_quotation.save') : __('purchase_quotation.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
