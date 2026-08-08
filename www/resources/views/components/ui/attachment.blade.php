@props([
    'title',
    'description' => null,
    'icon' => 'receipt',
    'state' => 'done',
    'progress' => null,
])

@php
    $resolvedState = in_array($state, ['idle', 'uploading', 'processing', 'error', 'done'], true) ? $state : 'done';
    $stateLabels = [
        'idle' => 'Pronto',
        'uploading' => 'Enviando',
        'processing' => 'Processando',
        'error' => 'Falha',
        'done' => 'Concluído',
    ];
@endphp

<article {{ $attributes->class(['ui-attachment', 'ui-attachment-'.$resolvedState]) }} data-ui-attachment>
    <span class="ui-attachment-media"><x-ui.icon :name="$icon" /></span>
    <div class="ui-attachment-content">
        <div class="flex min-w-0 items-center gap-2">
            <strong class="ui-attachment-title">{{ $title }}</strong>
            <x-ui.badge :variant="$resolvedState === 'error' ? 'danger' : ($resolvedState === 'done' ? 'success' : 'info')" size="sm">{{ $stateLabels[$resolvedState] }}</x-ui.badge>
        </div>
        @if($description)<p class="ui-attachment-description">{{ $description }}</p>@endif
        @if($progress !== null)
            <x-ui.progress class="mt-2" :value="$progress" size="sm" :show-value="false" :variant="$resolvedState === 'error' ? 'danger' : 'primary'" />
        @endif
    </div>
    @isset($actions)
        <div class="ui-attachment-actions">{{ $actions }}</div>
    @endisset
</article>
