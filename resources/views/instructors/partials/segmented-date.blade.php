@php
    $dateString = $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : (string) ($value ?? '');
    $dateParts = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString) ? explode('-', $dateString) : ['', '', ''];
    [$selectedYear, $selectedMonth, $selectedDay] = $dateParts;
    $monthNames = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $firstYear = (int) date('Y') + 15;
    $lastYear = 1950;
@endphp
<div @class(['field', 'segmented-date', $class ?? null]) data-segmented-date>
    <span id="{{ $id }}-label">{{ $label }}</span>
    <label class="segmented-date__calendar">
        <span>Calendar</span>
        <input type="date" name="{{ $name }}" value="{{ $dateString }}" min="{{ $lastYear }}-01-01" max="{{ $firstYear }}-12-31" data-date-value aria-label="{{ $label }} calendar">
    </label>
    <div class="segmented-date__controls" role="group" aria-labelledby="{{ $id }}-label">
        <select data-date-month aria-label="{{ $label }} month">
            <option value="">Month</option>
            @foreach($monthNames as $monthNumber => $monthName)
                <option value="{{ str_pad((string) $monthNumber, 2, '0', STR_PAD_LEFT) }}" @selected((int) $selectedMonth === $monthNumber)>{{ $monthName }}</option>
            @endforeach
        </select>
        <select data-date-day aria-label="{{ $label }} day">
            <option value="">Day</option>
            @for($day = 1; $day <= 31; $day++)
                <option value="{{ str_pad((string) $day, 2, '0', STR_PAD_LEFT) }}" @selected((int) $selectedDay === $day)>{{ $day }}</option>
            @endfor
        </select>
        <select data-date-year aria-label="{{ $label }} year">
            <option value="">Year</option>
            @for($year = $firstYear; $year >= $lastYear; $year--)
                <option value="{{ $year }}" @selected((int) $selectedYear === $year)>{{ $year }}</option>
            @endfor
        </select>
    </div>
</div>

