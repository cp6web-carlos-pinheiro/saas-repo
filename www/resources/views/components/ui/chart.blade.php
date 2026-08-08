@props([
    'type' => 'bar',
    'data' => [],
    'labels' => [],
    'label' => 'Gráfico',
    'suffix' => '',
    'summary' => null,
])

@php
    $values = array_map(static fn ($value) => max(0, (float) $value), array_values($data));
    $resolvedLabels = array_values($labels);
    $maxValue = max(1, ...$values);
    $count = max(1, count($values));
    $points = [];
    $donutColors = ['var(--ui-primary)', 'var(--ui-success)', 'var(--ui-warning)', 'var(--ui-info)', 'var(--ui-danger)', 'var(--ui-neutral)'];
    $total = array_sum($values);
    $donutStops = [];
    $donutCursor = 0;

    foreach ($values as $index => $value) {
        $x = $count === 1 ? 50 : ($index / ($count - 1)) * 100;
        $y = 46 - (($value / $maxValue) * 40);
        $points[] = round($x, 2).','.round($y, 2);
    }

    foreach ($values as $index => $value) {
        $start = $donutCursor;
        $donutCursor += $total > 0 ? ($value / $total) * 100 : 0;
        $donutStops[] = $donutColors[$index % count($donutColors)].' '.round($start, 2).'% '.round($donutCursor, 2).'%';
    }

    $donutBackground = $total > 0
        ? 'conic-gradient('.implode(', ', $donutStops).')'
        : 'var(--ui-surface-muted)';
    $areaPoints = '0,46 '.implode(' ', $points).' 100,46';
@endphp

<figure {{ $attributes->class(['ui-chart']) }} aria-label="{{ $label }}">
    <figcaption class="ui-chart-caption">
        <span>{{ $label }}</span>
        @if($summary)<strong>{{ $summary }}</strong>@endif
    </figcaption>

    @if(in_array($type, ['line', 'area'], true))
        <div class="ui-chart-cartesian">
            <svg class="ui-chart-line {{ $type === 'area' ? 'ui-chart-area' : '' }}" viewBox="0 0 100 50" role="img" aria-label="{{ $label }}">
                <line x1="0" y1="46" x2="100" y2="46" class="ui-chart-axis" />
                @if($type === 'area')<polygon points="{{ $areaPoints }}" class="ui-chart-area-fill" />@endif
                <polyline points="{{ implode(' ', $points) }}" class="ui-chart-line-path" />
                @foreach($values as $index => $value)
                    @php
                        [$x, $y] = explode(',', $points[$index]);
                        $pointLabel = $resolvedLabels[$index] ?? ($index + 1);
                    @endphp
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="1.8" class="ui-chart-point"><title>{{ $pointLabel }}: {{ $value + 0 }}{{ $suffix }}</title></circle>
                @endforeach
            </svg>
            <div class="ui-chart-labels" aria-hidden="true">
                @foreach($values as $index => $value)<small>{{ $resolvedLabels[$index] ?? ($index + 1) }}</small>@endforeach
            </div>
        </div>
    @elseif($type === 'horizontal')
        <div class="ui-chart-horizontal" role="img" aria-label="{{ $label }}">
            @foreach($values as $index => $value)
                @php($barLabel = $resolvedLabels[$index] ?? ($index + 1))
                <div class="ui-chart-horizontal-row">
                    <div class="ui-chart-horizontal-meta"><span>{{ $barLabel }}</span><strong>{{ $value + 0 }}{{ $suffix }}</strong></div>
                    <div class="ui-chart-horizontal-track">
                        <span class="ui-chart-horizontal-bar" tabindex="0" style="width: {{ max(3, ($value / $maxValue) * 100) }}%" aria-label="{{ $barLabel }}: {{ $value + 0 }}{{ $suffix }}"></span>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($type === 'donut')
        <div class="ui-chart-donut-layout">
            <div class="ui-chart-donut" role="img" aria-label="{{ $label }}" style="background: {{ $donutBackground }}">
                <span class="ui-chart-donut-center"><strong>{{ $total + 0 }}</strong><small>{{ trim($suffix) }}</small></span>
            </div>
            <ul class="ui-chart-legend" aria-label="Legenda de {{ $label }}">
                @foreach($values as $index => $value)
                    <li><span class="ui-chart-legend-swatch" style="background: {{ $donutColors[$index % count($donutColors)] }}"></span><span>{{ $resolvedLabels[$index] ?? ($index + 1) }}</span><strong>{{ $value + 0 }}</strong></li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="ui-chart-bars" role="img" aria-label="{{ $label }}">
            @foreach($values as $index => $value)
                @php($barLabel = $resolvedLabels[$index] ?? ($index + 1))
                <div class="ui-chart-column">
                    <span class="ui-chart-bar" tabindex="0" style="height: {{ max(3, ($value / $maxValue) * 100) }}%">
                        <span class="ui-chart-tooltip">{{ $barLabel }}: {{ $value + 0 }}{{ $suffix }}</span>
                    </span>
                    <small>{{ $barLabel }}</small>
                </div>
            @endforeach
        </div>
    @endif
    <table class="sr-only">
        <caption>{{ $label }}</caption>
        <tbody>@foreach($values as $index => $value)<tr><th>{{ $resolvedLabels[$index] ?? ($index + 1) }}</th><td>{{ $value + 0 }}{{ $suffix }}</td></tr>@endforeach</tbody>
    </table>
</figure>
