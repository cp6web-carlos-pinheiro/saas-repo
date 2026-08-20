@extends('layouts.client-area')

@php($editing = $day !== null)

@section('title', __('ui.module_production').' | '.__('ui.production_calendar'))
@section('client-page-title', $editing ? __('production.calendar.edit') : __('production.calendar.new'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('production.calendar.edit') : __('production.calendar.new') }}</h1>
        <x-ui.button :href="$editing ? route('production.calendar.show', $day) : route('production.calendar.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('production.calendar.update', $day) : route('production.calendar.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <label class="block text-sm font-medium">{{ __('production.work_center') }}
                <x-ui.select class="mt-2" name="work_center_id" data-search="on" required>
                    <option value="">{{ __('production.select') }}</option>
                    @foreach ($workCenters as $center)
                        <option value="{{ $center->id }}" @selected((string) old('work_center_id', $day?->work_center_id) === (string) $center->id)>{{ $center->code }} - {{ $center->name }}</option>
                    @endforeach
                </x-ui.select>
            </label>

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">{{ __('production.date') }}
                    <x-ui.input class="mt-2" type="date" name="calendar_date" :value="old('calendar_date', optional($day?->calendar_date)->format('Y-m-d'))" required />
                </label>
                <label class="block text-sm font-medium">{{ __('production.calendar.available_capacity') }}
                    <x-ui.input class="mt-2" type="number" step="0.01" min="0" name="available_capacity" :value="old('available_capacity', $day?->available_capacity)" />
                </label>
            </div>

            <label class="block text-sm font-medium">{{ __('production.notes') }}
                <x-ui.input class="mt-2" name="notes" maxlength="255" :value="old('notes', $day?->notes)" />
            </label>

            <div>
                <x-ui.input type="hidden" name="is_working_day" value="0" unstyled />
                <x-ui.checkbox name="is_working_day" value="1" :checked="(bool) old('is_working_day', $day?->is_working_day ?? true)">{{ __('production.work_centers.working_day') }}</x-ui.checkbox>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('production.calendar.show', $day) : route('production.calendar.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('production.save') : __('production.calendar.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
