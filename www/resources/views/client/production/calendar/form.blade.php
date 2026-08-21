@extends('layouts.client-area')

@php($editing = $day !== null)

@section('title', __('ui.module_production').' | '.__('ui.production_calendar'))
@section('client-page-title', $editing ? __('production.calendar.edit') : __('production.calendar.new'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $editing ? __('production.calendar.edit') : __('production.calendar.new') }}">
        <x-slot:actions>
        <x-ui.button :href="$editing ? route('production.calendar.show', $day) : route('production.calendar.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('production.calendar.update', $day) : route('production.calendar.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-ui.field label="{{ __('production.work_center') }}" for="work-center-id" :required="true" :error="$errors->first('work_center_id')">
                <x-ui.select class="mt-2" name="work_center_id" data-search="on" required id="work-center-id" :aria-describedby="$errors->has('work_center_id') ? 'work-center-id-error' : null">
                    <option value="">{{ __('production.select') }}</option>
                    @foreach ($workCenters as $center)
                        <option value="{{ $center->id }}" @selected((string) old('work_center_id', $day?->work_center_id) === (string) $center->id)>{{ $center->code }} - {{ $center->name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="{{ __('production.date') }}" for="calendar-date" :required="true" :error="$errors->first('calendar_date')">
                    <x-ui.date-picker class="mt-2" name="calendar_date" :value="old('calendar_date', optional($day?->calendar_date)->format('Y-m-d'))" required id="calendar-date" :aria-describedby="$errors->has('calendar_date') ? 'calendar-date-error' : null" />
                </x-ui.field>
                <x-ui.field label="{{ __('production.calendar.available_capacity') }}" for="available-capacity" :error="$errors->first('available_capacity')">
                    <x-ui.input class="mt-2" type="number" step="0.01" min="0" name="available_capacity" :value="old('available_capacity', $day?->available_capacity)"  id="available-capacity" :aria-describedby="$errors->has('available_capacity') ? 'available-capacity-error' : null"/>
                </x-ui.field>
            </div>

            <x-ui.field label="{{ __('production.notes') }}" for="notes" :error="$errors->first('notes')">
                <x-ui.input class="mt-2" name="notes" maxlength="255" :value="old('notes', $day?->notes)"  id="notes" :aria-describedby="$errors->has('notes') ? 'notes-error' : null"/>
            </x-ui.field>

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
