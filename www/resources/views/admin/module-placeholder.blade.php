@extends('layouts.global-admin')

@section('title', __('global_admin.modules.'.$module).' | '.__('ui.app_name'))

@section('admin-content')
  <section class="flex min-h-[calc(100vh-10rem)] items-center justify-center">
    <div class="max-w-lg text-center"><p class="text-sm text-[#5f6368]">{{ __('global_admin.title') }}</p><h1 class="mt-2 font-display text-3xl font-bold">{{ __('global_admin.modules.'.$module) }}</h1><p class="mt-4 text-[#5f6368]">{{ __('global_admin.module_coming_soon') }}</p></div>
  </section>
@endsection
