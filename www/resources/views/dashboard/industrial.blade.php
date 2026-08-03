@extends('layouts.client-area')

@section('title', __('ui.dashboard').' | '.__('ui.app_name'))
@section('client-page-title', __('ui.dashboard'))

@section('client-content')
<div class="ind-content">
    @if (session('status'))
        <div class="ind-status-banner">{{ session('status') }}</div>
    @endif

    <h1 class="ind-welcome">{{ __('ui.welcome') }}</h1>
</div>
@endsection
