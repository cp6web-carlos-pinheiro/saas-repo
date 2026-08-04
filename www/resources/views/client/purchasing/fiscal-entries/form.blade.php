@extends('layouts.client-area')

@php($editing = $entry !== null)

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_fiscal_entry'))
@section('client-page-title', $editing ? __('purchase_fiscal_entry.edit') : __('purchase_fiscal_entry.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('purchase_fiscal_entry.edit') : __('purchase_fiscal_entry.create') }}</h1>
        <x-ui.button :href="$editing ? route('purchasing.fiscal-entries.show', $entry) : route('purchasing.fiscal-entries.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('purchasing.fiscal-entries.update', $entry) : route('purchasing.fiscal-entries.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('purchase_fiscal_entry.supplier') }}
                    <x-ui.select name="supplier_id" class="mt-2">
                        <option value="">{{ __('purchase_fiscal_entry.select_supplier') }}</option>
                        @foreach ($suppliers as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('supplier_id', $entry?->supplier_id) === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('supplier_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_fiscal_entry.order') }}
                    <x-ui.select name="purchase_order_id" class="mt-2">
                        <option value="">{{ __('purchase_fiscal_entry.select_order') }}</option>
                        @foreach ($orders as $id => $number)
                            <option value="{{ $id }}" @selected((string) old('purchase_order_id', $entry?->purchase_order_id) === (string) $id)>{{ $number }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('purchase_order_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-4">
                <label class="block text-sm font-medium">
                    {{ __('purchase_fiscal_entry.document_number') }}
                    <x-ui.input name="document_number" :value="old('document_number', $entry?->document_number)" class="mt-2" />
                    @error('document_number')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_fiscal_entry.issue_date') }}
                    <x-ui.input type="date" name="issue_date" :value="old('issue_date', $entry?->issue_date?->format('Y-m-d'))" class="mt-2" />
                    @error('issue_date')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_fiscal_entry.entry_date') }}
                    <x-ui.input type="date" name="entry_date" :value="old('entry_date', $entry?->entry_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" class="mt-2" required />
                    @error('entry_date')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_fiscal_entry.amount') }}
                    <x-ui.input name="amount" :value="old('amount', $editing ? number_format(((int) $entry->amount_cents) / 100, 2, ',', '.') : '0,00')" class="mt-2" required />
                    @error('amount')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('purchase_fiscal_entry.status') }}
                    <x-ui.select name="status" class="mt-2" required data-search="off">
                        <option value="DRAFT" @selected(old('status', $entry?->status ?? 'DRAFT') === 'DRAFT')>{{ __('purchase_fiscal_entry.status_draft') }}</option>
                        <option value="POSTED" @selected(old('status', $entry?->status ?? 'DRAFT') === 'POSTED')>{{ __('purchase_fiscal_entry.status_posted') }}</option>
                        <option value="CANCELLED" @selected(old('status', $entry?->status ?? 'DRAFT') === 'CANCELLED')>{{ __('purchase_fiscal_entry.status_cancelled') }}</option>
                    </x-ui.select>
                    @error('status')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_fiscal_entry.notes') }}
                    <x-ui.textarea name="notes" class="mt-2" rows="4">{{ old('notes', $entry?->notes) }}</x-ui.textarea>
                    @error('notes')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('purchasing.fiscal-entries.show', $entry) : route('purchasing.fiscal-entries.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('purchase_fiscal_entry.save') : __('purchase_fiscal_entry.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
