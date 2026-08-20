@props([
    'label' => __('ui.theme'),
])

<section {{ $attributes }}>
    <h3 class="font-semibold text-[var(--ui-text)]">{{ $label }}</h3>
    <div class="mt-4 grid grid-cols-3 gap-3" role="group" aria-label="{{ $label }}">
        <button type="button" class="ds-theme-choice" data-theme-option="light" aria-label="{{ __('ui.theme_light') }}">
            <x-ui.icon name="sun" />
            <span>{{ __('ui.theme_light') }}</span>
        </button>
        <button type="button" class="ds-theme-choice" data-theme-option="system" aria-label="{{ __('ui.theme_system') }}">
            <x-ui.icon name="device-desktop" />
            <span>{{ __('ui.theme_system') }}</span>
        </button>
        <button type="button" class="ds-theme-choice" data-theme-option="dark" aria-label="{{ __('ui.theme_dark') }}">
            <x-ui.icon name="moon" />
            <span>{{ __('ui.theme_dark') }}</span>
        </button>
    </div>
</section>
