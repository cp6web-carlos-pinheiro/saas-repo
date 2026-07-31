@extends('layouts.global-admin')
@section('title', __('global_admin.edit').' | '.__('ui.app_name'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
	<x-ui.breadcrumb :items="[['label' => __('global_admin.title'), 'href' => route('global-admin.administrators.index')], ['label' => __('global_admin.edit')]]" />

	<x-ui.panel class="mt-6 border-slate-200 shadow-sm" padding="p-6 md:p-8">
		<h1 class="font-display text-3xl font-bold">{{ __('global_admin.edit') }}</h1>
		<p class="mt-2 text-sm text-slate-500">{{ $administrator->email }}</p>

		@if ($errors->any())
			<x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
		@endif

		<form method="POST" action="{{ route('global-admin.administrators.update', $administrator) }}" class="mt-6 space-y-4">
			@csrf
			@method('PUT')

			<label class="block text-sm font-medium">{{ __('global_admin.name') }}<input name="name" value="{{ old('name', $administrator->name) }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
			<label class="block text-sm font-medium">{{ __('global_admin.email') }}<input name="email" type="email" value="{{ old('email', $administrator->email) }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
			<label class="block text-sm font-medium">{{ __('global_admin.new_password') }}<input name="password" type="password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
			<label class="block text-sm font-medium">{{ __('global_admin.password_confirmation') }}<input name="password_confirmation" type="password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
			<label class="flex items-center gap-2 text-sm"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $administrator->is_active))>{{ __('global_admin.active') }}</label>

			<x-ui.button type="submit" variant="brand-primary" :full="true" class="rounded-full">{{ __('global_admin.save') }}</x-ui.button>
		</form>

		<form method="POST" action="{{ route('global-admin.administrators.destroy', $administrator) }}" class="mt-4" data-admin-delete-confirm data-admin-name="{{ $administrator->name }}" data-confirm-title="{{ __('global_admin.confirm_delete_title') }}" data-confirm-text="{{ __('global_admin.confirm_delete_text') }}" data-confirm-confirm="{{ __('global_admin.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('global_admin.confirm_delete_cancel') }}">
			@csrf
			@method('DELETE')
			<x-ui.button type="submit" variant="danger-outline" :full="true" class="rounded-full">{{ __('global_admin.remove') }}</x-ui.button>
		</form>
	</x-ui.panel>
</div>
@endsection
