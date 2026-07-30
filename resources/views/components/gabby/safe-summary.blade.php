@props(['parts'])

@foreach ($parts as $part)
    @if ($part['type'] === 'link')
        <x-gabby.source-link class="gabby-inline-link" :href="$part['url']" :label="$part['label']" />
    @else
        {{ $part['value'] }}
    @endif
@endforeach
