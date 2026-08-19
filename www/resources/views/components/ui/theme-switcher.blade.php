<label {{ $attributes->class(['ui-theme-switcher']) }}>
    <span class="sr-only">{{ __('ui.theme') }}</span>
    <x-ui.icon name="palette" size="sm" aria-hidden="true" />
    <select class="ui-theme-switcher-select" data-ui-theme-select aria-label="{{ __('ui.theme') }}">
        <option value="light">{{ __('ui.theme_light') }}</option>
        <option value="system">{{ __('ui.theme_system') }}</option>
        <option value="dark">{{ __('ui.theme_dark') }}</option>
    </select>
</label>
