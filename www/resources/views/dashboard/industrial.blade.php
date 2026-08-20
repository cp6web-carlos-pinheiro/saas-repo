@extends('layouts.client-area')

@section('title', __('ui.dashboard').' | '.__('ui.app_name'))
@section('client-page-title', __('ui.dashboard'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    @if (session('status'))
        <x-ui.alert class="mb-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.page-heading :title="__('ui.welcome')" :subtitle="__('ui.dashboard')" />
</div>
@endsection
