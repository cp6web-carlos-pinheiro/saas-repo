@props([
    'label' => 'Grupo de ações',
    'orientation' => 'horizontal',
    'variant' => 'joined',
    'tone' => 'primary',
])

<div
    role="group"
    aria-label="{{ $label }}"
    {{ $attributes->class([
        'ui-button-group',
        'ui-button-group-vertical' => $orientation === 'vertical',
        'ui-button-group-segmented' => $variant === 'segmented',
        'ui-button-group-joined' => $variant === 'joined',
        'ui-button-group-joined-'.$tone => $variant === 'joined' && in_array($tone, ['primary', 'outline', 'surface'], true),
        'ui-button-group-pill' => $variant === 'pill',
        'ui-button-group-pill-'.$tone => $variant === 'pill' && in_array($tone, ['primary', 'outline', 'surface'], true),
    ]) }}
>
    {{ $slot }}
</div>
