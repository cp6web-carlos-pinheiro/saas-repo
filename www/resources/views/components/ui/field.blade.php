@props([
    'label',
    'for' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
])

<div {{ $attributes->class(['ui-field'])->merge(['data-ui-field' => true, 'data-for' => $for]) }}>
    <label @if($for) for="{{ $for }}" @endif class="ui-field-label">
        {{ $label }}
        @if ($required)
            <span class="text-[var(--ui-danger)]" aria-hidden="true">*</span>
            <span class="sr-only">obrigatório</span>
        @endif
    </label>
    <div class="mt-2">{{ $slot }}</div>
    @if ($error)
        <p @if($for) id="{{ $for }}-error" @endif class="ui-field-error mt-2">{{ $error }}</p>
    @elseif ($hint)
        <p @if($for) id="{{ $for }}-hint" @endif class="ui-field-hint mt-2">{{ $hint }}</p>
    @endif
</div>