@extends('layouts.global-admin')

@section('title', __('global_admin.home').' | '.__('ui.app_name'))

@section('admin-content')
  <section class="flex min-h-[calc(100vh-10rem)] items-center justify-center">
    <div class="text-center">
      <span class="inline-grid h-14 w-14 place-items-center rounded-2xl bg-[#e8f0fe] text-2xl font-bold text-[#1a73e8]">B</span>
      <p class="mt-6 text-sm text-[#5f6368]">{{ __('global_admin.title') }}</p>
      <h1 class="mt-2 font-display text-3xl font-bold md:text-4xl">Bem-vindo à área de Administração</h1>
    </div>
  </section>
@endsection
