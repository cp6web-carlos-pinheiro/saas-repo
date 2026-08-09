@props([
    'align' => 'left',
    'editable' => false,
    'copyable' => false,
    'value' => null,
    'inputType' => 'text',
    'inputName' => null,
])

@php
    $isEditable = (bool) $editable;
    $isCopyable = (bool) $copyable;
    $isInteractive = $isEditable || $isCopyable;
    $resolvedValue = $value !== null
        ? (string) $value
        : trim(html_entity_decode(strip_tags((string) $slot)));
@endphp

<td
    {{ $attributes->class(['ui-table-cell', 'text-right' => $align === 'right', 'text-center' => $align === 'center']) }}
    @if($isInteractive) data-ui-table-cell data-ui-table-cell-value="{{ $resolvedValue }}" @endif
>
    @if(!$isInteractive)
        {{ $slot }}
    @else
        <div @class(['ui-table-cell-inline', 'ui-table-cell-inline-end' => $align === 'right', 'ui-table-cell-inline-center' => $align === 'center'])>
            <span class="ui-table-cell-display" data-ui-table-cell-display>{{ $slot }}</span>
            @if($isEditable)
                <input
                    type="{{ $inputType }}"
                    value="{{ $resolvedValue }}"
                    @if($inputName) name="{{ $inputName }}" @endif
                    class="ui-table-cell-input hidden"
                    aria-label="{{ __('ui.edit') }}: {{ $resolvedValue }}"
                    data-ui-table-cell-input
                />
            @endif
            <span class="ui-table-cell-actions">
                @if($isCopyable)
                    <button type="button" class="ui-table-cell-action" title="{{ __('ui.copy') }}" aria-label="{{ __('ui.copy') }}: {{ $resolvedValue }}" data-ui-table-cell-copy>
                        <x-ui.icon name="copy" size="xs" />
                    </button>
                @endif
                @if($isEditable)
                    <button type="button" class="ui-table-cell-action" title="{{ __('ui.edit') }}" aria-label="{{ __('ui.edit') }}: {{ $resolvedValue }}" data-ui-table-cell-edit>
                        <x-ui.icon name="pencil" size="xs" />
                    </button>
                    <button type="button" class="ui-table-cell-action ui-table-cell-action-save hidden" title="{{ __('ui.save') }}" aria-label="{{ __('ui.save') }}" data-ui-table-cell-save>
                        <x-ui.icon name="circle-check" size="xs" />
                    </button>
                    <button type="button" class="ui-table-cell-action ui-table-cell-action-cancel hidden" title="{{ __('ui.cancel') }}" aria-label="{{ __('ui.cancel') }}" data-ui-table-cell-cancel>
                        <x-ui.icon name="x" size="xs" />
                    </button>
                @endif
            </span>
        </div>
    @endif
</td>
