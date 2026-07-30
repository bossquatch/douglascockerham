@props(['href', 'label'])

<a
    href="{{ $href }}"
    target="_blank"
    rel="noopener noreferrer"
    {{ $attributes->class(['gabby-external-link']) }}
>
    <span>{{ $label }}</span>
    <svg aria-hidden="true" viewBox="0 0 24 24">
        <path d="M14 5h5v5M19 5l-8 8M18 13v5H6V6h5"/>
    </svg>
    <span class="sr-only"> (opens in a new tab)</span>
</a>
