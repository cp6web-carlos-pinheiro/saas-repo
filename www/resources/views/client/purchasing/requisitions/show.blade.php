@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_requisition'))
@section('client-page-title', __('purchase_requisition.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $requisition->requisition_number }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('purchasing.requisitions.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('purchasing.requisitions.edit', $requisition)" variant="material-edit" class="rounded-full">{{ __('purchase_requisition.edit') }}</x-ui.button>
            <form method="POST" action="{{ route('purchasing.requisitions.destroy', $requisition) }}" data-admin-delete-confirm data-admin-name="{{ $requisition->requisition_number }}" data-confirm-title="{{ __('purchase_requisition.confirm_delete_title') }}" data-confirm-text="{{ __('purchase_requisition.confirm_delete_text') }}" data-confirm-confirm="{{ __('purchase_requisition.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('purchase_requisition.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('purchase_requisition.remove') }}</x-ui.button>
            </form>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('purchase_requisition.reference')">#{{ $requisition->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_requisition.number')">{{ $requisition->requisition_number }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_requisition.status')">{{ __('purchase_requisition.status_'.strtolower($requisition->status)) }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_requisition.required_date')">{{ $requisition->required_date?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_requisition.source_type')">{{ $requisition->source_type ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_requisition.lines_count')">{{ $requisition->lines_count }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_requisition.notes')">{{ $requisition->notes ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('purchase_requisition.created_at')" :value="$requisition->created_at" />
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
