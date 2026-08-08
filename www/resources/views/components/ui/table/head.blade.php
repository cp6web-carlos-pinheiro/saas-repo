@props(['align' => 'left'])

<th scope="col" {{ $attributes->class(['ui-table-head', 'text-right' => $align === 'right', 'text-center' => $align === 'center']) }}>{{ $slot }}</th>
