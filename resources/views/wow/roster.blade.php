@php
    $roster = session('wow_battle_net_roster');
    $connected = ($roster['user'] ?? null) === auth()->id();
@endphp

<section class="panel" id="my-characters">
    <h2>@include('wow.icon', ['icon' => 'character']) My Characters</h2>
    @if(session('wow_roster_error'))
        <p role="alert">{{ session('wow_roster_error') }}</p>
    @endif

    @if($connected)
        <p class="muted">WoW Forever · US · Imported {{ \Illuminate\Support\Carbon::parse($roster['imported_at'])->utc()->format('M j, H:i') }} UTC</p>
        <div class="recipe-catalog">
            @forelse($roster['characters'] as $alt)
                <article class="recipe-card">
                    <h3>
                        @if($alt['surname'] !== '')
                            <a href="{{ route('wow.lookup', ['first_name' => $alt['first_name'], 'surname' => $alt['surname'], 'region' => 'us']) }}">{{ $alt['first_name'] }} {{ $alt['surname'] }}</a>
                        @else
                            {{ $alt['first_name'] }}
                        @endif
                    </h3>
                    @if($alt['level'] > 0)
                        <p class="muted">Level {{ $alt['level'] }}</p>
                    @endif
                </article>
            @empty
                <p>Battle.net returned no US WoW Forever characters for this account.</p>
            @endforelse
        </div>
    @else
        <p class="muted">Connect Battle.net to import your US WoW Forever roster into this private session.</p>
    @endif

    <div class="toolbar">
        <form method="post" action="{{ route('wow.roster.connect') }}">
            @csrf
            <button type="submit">{{ $connected ? 'Reimport roster' : 'Connect Battle.net' }}</button>
        </form>
        @if($connected)
            <form method="post" action="{{ route('wow.roster.disconnect') }}">
                @csrf
                <button type="submit">Clear roster</button>
            </form>
        @endif
    </div>
</section>
