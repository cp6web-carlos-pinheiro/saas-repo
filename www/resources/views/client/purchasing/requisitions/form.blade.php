@extends('layouts.client-area')

@php($editing = $requisition !== null)

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_requisition'))
@section('client-page-title', $editing ? __('purchase_requisition.edit') : __('purchase_requisition.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('purchase_requisition.edit') : __('purchase_requisition.create') }}</h1>
        <x-ui.button :href="$editing ? route('purchasing.requisitions.show', $requisition) : route('purchasing.requisitions.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('purchasing.requisitions.update', $requisition) : route('purchasing.requisitions.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('purchase_requisition.required_date') }}
                    <x-ui.input type="date" name="required_date" :value="old('required_date', $requisition?->required_date?->format('Y-m-d'))" class="mt-2" />
                    @error('required_date')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_requisition.status') }}
                    <x-ui.select name="status" class="mt-2" required data-search="off">
                        <option value="DRAFT" @selected(old('status', $requisition?->status ?? 'DRAFT') === 'DRAFT')>{{ __('purchase_requisition.status_draft') }}</option>
                        <option value="APPROVED" @selected(old('status', $requisition?->status ?? 'DRAFT') === 'APPROVED')>{{ __('purchase_requisition.status_approved') }}</option>
                        <option value="CANCELLED" @selected(old('status', $requisition?->status ?? 'DRAFT') === 'CANCELLED')>{{ __('purchase_requisition.status_cancelled') }}</option>
                    </x-ui.select>
                    @error('status')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('purchase_requisition.source_type') }}
                <x-ui.input name="source_type" :value="old('source_type', $requisition?->source_type ?? 'manual')" class="mt-2" />
                @error('source_type')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('purchase_requisition.notes') }}
                <x-ui.textarea name="notes" class="mt-2" rows="4">{{ old('notes', $requisition?->notes) }}</x-ui.textarea>
                @error('notes')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('purchasing.requisitions.show', $requisition) : route('purchasing.requisitions.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('purchase_requisition.save') : __('purchase_requisition.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
