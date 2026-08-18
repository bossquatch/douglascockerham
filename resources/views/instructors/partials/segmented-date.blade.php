@php
    $dateString = $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : (string) ($value ?? '');
    $firstYear = (int) date('Y') + 15;
    $lastYear = 1950;
    $maxDate = $max ?? "{$firstYear}-12-31";
@endphp
<label @class(['field', 'calendar-date', $class ?? null]) for="{{ $id }}">
    <span>{{ $label }}</span>
    <input id="{{ $id }}" type="date" name="{{ $name }}" value="{{ $dateString }}" min="{{ $lastYear }}-01-01" max="{{ $maxDate }}">
</label>



