@extends('layouts.public')

@section('bodyClass', 'min-h-screen bg-[#f8fafd] text-[#202124]')

@section('content')
  <div class="min-h-screen md:grid md:grid-cols-[280px_1fr] md:transition-[grid-template-columns] md:duration-300" data-admin-sidebar-shell>
    <aside class="hidden border-r border-[#dadce0] bg-white p-5 md:sticky md:top-0 md:flex md:h-screen md:flex-col md:overflow-hidden md:transition-all md:duration-300" data-admin-sidebar>
      <div class="flex items-start justify-between gap-2" data-admin-sidebar-topbar>
        <a href="{{ route('global-admin.home') }}" class="flex min-w-0 items-center gap-3 px-3 py-3 no-underline text-[#202124]" data-admin-sidebar-link data-admin-sidebar-content>
          <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-[#e8f0fe] text-lg font-bold text-[#1a73e8]">B</span>
          <span class="min-w-0" data-admin-sidebar-label>
            <strong class="block truncate text-base font-medium">{{ __('ui.app_name') }}</strong>
            <small class="block truncate text-xs text-[#5f6368]">{{ __('global_admin.title') }}</small>
          </span>
        </a>
        <button
          type="button"
          class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-[#5f6368] transition hover:bg-[#f1f3f4]"
          data-admin-sidebar-toggle
          aria-expanded="true"
          aria-label="{{ __('global_admin.collapse_sidebar') }}"
          title="{{ __('global_admin.toggle_sidebar') }}"
          data-collapse-label="{{ __('global_admin.collapse_sidebar') }}"
          data-expand-label="{{ __('global_admin.expand_sidebar') }}"
        >
          <span aria-hidden="true" data-admin-sidebar-toggle-icon>←</span>
        </button>
      </div>

      <div class="mt-8 flex min-h-0 flex-1 flex-col" data-admin-sidebar-content>
        <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto pr-1" aria-label="{{ __('global_admin.title') }}">
          <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wider text-[#5f6368]" data-admin-sidebar-label>{{ __('global_admin.management') }}</p>
          @php($links = [['home', route('global-admin.home'), '⌂'], ['modules.customers', route('global-admin.customers.index'), '♙'], ['modules.companies', route('global-admin.companies.index'), '▣'], ['modules.plans', route('global-admin.plans.index'), '◇'], ['modules.administrators', route('global-admin.administrators.index'), '⚙']])
          @foreach($links as [$label, $href, $icon])
            <a href="{{ $href }}" data-admin-sidebar-link @class(['flex items-center gap-3 rounded-full px-4 py-3 text-sm font-semibold no-underline transition', 'bg-[#e8f0fe] text-[#174ea6]' => url()->current() === $href, 'text-[#5f6368] hover:bg-[#f1f3f4]' => url()->current() !== $href])><span class="grid h-5 w-5 shrink-0 place-items-center text-base" aria-hidden="true">{{ $icon }}</span><span data-admin-sidebar-label>{{ __('global_admin.'.$label) }}</span></a>
          @endforeach
        </nav>

        <div class="border-t border-[#dadce0] pt-5">
          <form method="POST" action="{{ route('global-admin.logout') }}">
            @csrf
            <x-ui.button type="submit" variant="surface-muted" :full="true" size="lg" class="justify-start gap-2 rounded-full text-[#5f6368]" data-admin-sidebar-logout>
              <span aria-hidden="true">⇥</span>
              <span data-admin-sidebar-label>{{ __('admin.logout') }}</span>
            </x-ui.button>
          </form>
        </div>
      </div>
    </aside>
    <div class="min-w-0">
      <header class="flex min-h-16 items-center justify-between border-b border-[#dadce0] bg-white px-5 md:hidden">
        <div><strong>{{ __('ui.app_name') }}</strong><span class="ml-2 text-sm text-[#5f6368]">{{ __('global_admin.title') }}</span></div>
        <form method="POST" action="{{ route('global-admin.logout') }}">
          @csrf
          <x-ui.button type="submit" variant="surface-muted" size="sm" class="rounded-full text-[#5f6368]">{{ __('admin.logout') }}</x-ui.button>
        </form>
      </header>
      <main class="@yield('admin-content-container-class', 'mx-auto w-full max-w-7xl') p-5 md:p-8">@yield('admin-content')</main>
    </div>
  </div>
@endsection
