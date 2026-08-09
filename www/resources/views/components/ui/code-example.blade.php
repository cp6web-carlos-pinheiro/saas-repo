@props([
    'title' => 'Como usar',
    'language' => 'Blade',
    'open' => false,
])

<details @if($open) open @endif {{ $attributes->class(['ui-code-example']) }}>
    <summary class="ui-code-example-summary">
        <span>
            <strong>{{ $title }}</strong>
            <small>{{ $language }}</small>
        </span>
        <span class="ui-code-example-action">Ver código</span>
    </summary>
    <div class="ui-code-example-body">
        <button type="button" class="ui-code-copy" data-ui-copy-code data-copy-label="Copiar" data-copied-label="Copiado" aria-live="polite">Copiar</button>
        <pre><code data-ui-code>{{ trim((string) $slot) }}</code></pre>
    </div>
</details>
