@props([
    'label' => 'Gabby Compass Beacon brand mark',
])

<svg
    {{ $attributes->class('gabby-compass-beacon') }}
    viewBox="0 0 48 48"
    role="img"
    aria-label="{{ $label }}"
    data-gabby-compass-beacon
>
    <circle class="gabby-compass-beacon__surface" cx="24" cy="24" r="23" />
    <path
        class="gabby-compass-beacon__g"
        d="M36.7 14.3A16.5 16.5 0 1 0 39.6 30.4V24H31.2"
    />
    <path
        class="gabby-compass-beacon__rose"
        d="m24 13.5 2.7 7.8 7.8 2.7-7.8 2.7-2.7 7.8-2.7-7.8-7.8-2.7 7.8-2.7Z"
    />
    <path class="gabby-compass-beacon__north" d="m24 13.5 2.7 7.8L24 24Z" />
    <circle class="gabby-compass-beacon__center" cx="24" cy="24" r="2.45" />
</svg>
