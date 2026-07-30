@props([
    'descriptor' => null,
    'context' => 'sidebar',
])

<div {{ $attributes->class(['gabby-brand-lockup', "gabby-brand-lockup--{$context}"]) }}>
    <x-gabby.compass-beacon />
    <div class="gabby-brand-lockup__copy">
        <span class="gabby-brand-lockup__wordmark">Gabby</span>
        @if ($descriptor)
            <span class="gabby-brand-lockup__descriptor">{{ $descriptor }}</span>
        @endif
    </div>
</div>
